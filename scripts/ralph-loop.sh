#!/usr/bin/env bash
# ralph-loop.sh — Autonomous phase loop orchestrator using opencode
#
# Iterates over every plan file in specs/, running execute-phase then
# verify-phase for each incomplete phase until all phases are done.
# Start phase is auto-detected from the last phase file that has completed tasks.
#
# Usage:
#   ./scripts/ralph-loop.sh
#
# Environment variables:
#   START_FROM_PHASE=2    Override auto-detection and start from a specific phase number
#   MAX_PHASES=N          Safety cap on the number of phases to process (default: 20)
#   OPENCODE_MODEL=model  Model to use in provider/model format (e.g. anthropic/claude-opus-4-5)

set -euo pipefail

# ─── Config ───────────────────────────────────────────────────────────────────

MAX_PHASES="${MAX_PHASES:-20}"
START_FROM_PHASE="${START_FROM_PHASE:-}"
OPENCODE_MODEL="${OPENCODE_MODEL:-}"

PROJECT_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SPECS_DIR="$PROJECT_ROOT/specs"
LOG_DIR="$PROJECT_ROOT/logs/ralph"

PHASES_COMPLETED=0

# ─── Helpers ──────────────────────────────────────────────────────────────────

log() {
    local msg="[$(date '+%Y-%m-%d %H:%M:%S')] $*"
    echo "$msg"
    mkdir -p "$LOG_DIR"
    echo "$msg" >> "$LOG_DIR/ralph-loop.log"
}

log_separator() {
    log "════════════════════════════════════════════════════════════════"
}

# Extract the phase number from a plan filename.
# e.g. Plan_v1___Phase_3__Maintenance.md → 3
extract_phase_num() {
    local filename="$1"
    echo "$filename" | sed -E 's/.*Phase_([0-9]+)__.*/\1/'
}

# Returns 0 (true) if the plan file has any unchecked task (## - [ ]).
has_incomplete_tasks() {
    local plan_file="$1"
    grep -q '^## - \[ \]' "$plan_file"
}

# Returns 0 (true) if the plan file has any checked task (## - [x]).
has_completed_tasks() {
    local plan_file="$1"
    grep -qi '^## - \[x\]' "$plan_file"
}

# Scan plan files and return the path of the first incomplete phase at or after
# $start_from. Prints an empty string when all phases are complete.
detect_next_phase_file() {
    local start_from="${1:-1}"

    while IFS= read -r plan_file; do
        local filename phase_num
        filename=$(basename "$plan_file")
        phase_num=$(extract_phase_num "$filename")

        if (( 10#$phase_num < 10#$start_from )); then
            continue
        fi

        if has_incomplete_tasks "$plan_file"; then
            echo "$plan_file"
            return 0
        fi
    done < <(find "$SPECS_DIR" -maxdepth 1 -name "Plan_v1___Phase_*.md" -type f | sort -V)

    echo ""
}

# Auto-detect the starting phase number.
# Strategy: find the last phase that has at least one completed [x] task.
# If that phase is fully complete, start from the next phase.
# If it has remaining [ ] tasks, start from that same phase.
# Falls back to phase 1 if no completed tasks are found anywhere.
auto_detect_start_phase() {
    local last_with_completed=0
    local last_fully_complete=0

    while IFS= read -r plan_file; do
        local filename phase_num
        filename=$(basename "$plan_file")
        phase_num=$(extract_phase_num "$filename")

        if has_completed_tasks "$plan_file"; then
            last_with_completed=$((10#$phase_num))
            if ! has_incomplete_tasks "$plan_file"; then
                last_fully_complete=$((10#$phase_num))
            fi
        fi
    done < <(find "$SPECS_DIR" -maxdepth 1 -name "Plan_v1___Phase_*.md" -type f | sort -V)

    if [[ $last_with_completed -eq 0 ]]; then
        # No completed tasks found anywhere — start from phase 1
        echo "1"
        return
    fi

    if [[ $last_fully_complete -eq $last_with_completed ]]; then
        # The last phase with completed tasks is fully done — start from next phase
        echo $(( last_fully_complete + 1 ))
    else
        # There is a partially complete phase — start from that phase
        echo "$last_with_completed"
    fi
}

# Build the opencode run flags array, excluding empty optional flags.
build_opencode_flags() {
    local flags=()
    if [[ -n "$OPENCODE_MODEL" ]]; then
        flags+=("--model" "$OPENCODE_MODEL")
    fi
    echo "${flags[@]:-}"
}

# Invoke opencode run with a named command and the plan file as its argument.
# Arguments: step_name command_name plan_file phase_log_prefix
run_opencode_step() {
    local step_name="$1"
    local command_name="$2"
    local plan_file="$3"
    local phase_log="$4"

    # Make the plan file path relative to the project root for portability
    local relative_plan_file
    relative_plan_file="${plan_file#"$PROJECT_ROOT/"}"

    log "  [$step_name] Running opencode command: $command_name $relative_plan_file"

    local step_log="$phase_log.${step_name}.log"
    local extra_flags
    extra_flags=$(build_opencode_flags)

    local exit_code=0
    # shellcheck disable=SC2086
    if ! opencode run \
            --command "$command_name" \
            $extra_flags \
            "$relative_plan_file" \
        2>&1 | tee "$step_log"; then
        exit_code=$?
    fi

    if [[ $exit_code -ne 0 ]]; then
        log "  [$step_name] FAILED (exit $exit_code). See $step_log for details."
        return 1
    fi

    log "  [$step_name] Completed successfully."
    return 0
}

# ─── Main Loop ────────────────────────────────────────────────────────────────

main() {
    mkdir -p "$LOG_DIR"

    log_separator
    log "Ralph Loop starting (opencode $( opencode --version 2>/dev/null || echo 'unknown' ))"
    log "  Project root : $PROJECT_ROOT"
    log "  Specs dir    : $SPECS_DIR"
    log "  MAX_PHASES   : $MAX_PHASES"
    log "  MODEL        : ${OPENCODE_MODEL:-default}"
    log_separator

    # ── Determine start phase ──────────────────────────────────────────────────

    local start_phase
    if [[ -n "$START_FROM_PHASE" ]]; then
        start_phase="$START_FROM_PHASE"
        log "Start phase  : $start_phase (from START_FROM_PHASE env var)"
    else
        start_phase=$(auto_detect_start_phase)
        log "Start phase  : $start_phase (auto-detected)"
    fi

    log_separator

    # ── Ensure we are on develop and it is clean ───────────────────────────────

    local current_branch
    current_branch=$(git -C "$PROJECT_ROOT" branch --show-current)

    # Create develop branch if it doesn't exist
    if ! git -C "$PROJECT_ROOT" show-ref --verify --quiet refs/heads/develop; then
        log "Branch 'develop' does not exist. Creating it from '$current_branch'..."
        git -C "$PROJECT_ROOT" checkout -b develop
    else
        # Switch to develop if not already on it
        if [[ "$current_branch" != "develop" ]]; then
            log "Not on develop branch (currently on '$current_branch'). Switching..."
            git -C "$PROJECT_ROOT" checkout develop
        fi
    fi

    # Pull latest from remote if available
    if git -C "$PROJECT_ROOT" remote get-url origin &>/dev/null; then
        log "Pulling latest from origin/develop..."
        git -C "$PROJECT_ROOT" pull origin develop || log "Note: origin/develop may not exist yet."
    fi

    # ── Phase loop ─────────────────────────────────────────────────────────────

    local phase_count=0

    while (( phase_count < MAX_PHASES )); do
        local plan_file
        plan_file=$(detect_next_phase_file "$start_phase")

        if [[ -z "$plan_file" ]]; then
            log_separator
            log "All phases complete!"
            log "  Total phases processed this run: $PHASES_COMPLETED"
            log_separator
            break
        fi

        local filename phase_num
        filename=$(basename "$plan_file")
        phase_num=$(extract_phase_num "$filename")
        # Derive a readable title by stripping prefix/suffix
        local phase_title
        phase_title=$(echo "$filename" \
            | sed -E 's/Plan_v1___Phase_[0-9]+__//' \
            | sed 's/_/ /g' \
            | sed 's/\.md$//')

        log_separator
        log "PHASE $phase_num: $phase_title"
        log_separator

        local phase_log="$LOG_DIR/phase-${phase_num}"

        # 1. Execute phase
        if ! run_opencode_step "execute-phase" "execute-phase" "$plan_file" "$phase_log"; then
            log "FATAL: execute-phase failed for phase $phase_num. Stopping loop."
            exit 1
        fi

        # 2. Verify phase
        if ! run_opencode_step "verify-phase" "verify-phase" "$plan_file" "$phase_log"; then
            log "FATAL: verify-phase failed for phase $phase_num. Stopping loop."
            exit 1
        fi

        PHASES_COMPLETED=$(( PHASES_COMPLETED + 1 ))
        phase_count=$(( phase_count + 1 ))

        # After a phase completes, reset start_phase to 1 to allow the scanner
        # to find the true next incomplete phase for the next iteration.
        start_phase=1

        log "  Phase $phase_num complete. ($PHASES_COMPLETED processed this run)"
    done

    if (( phase_count >= MAX_PHASES )); then
        log "WARNING: Reached MAX_PHASES ($MAX_PHASES) safety cap. Stopping."
        exit 1
    fi
}

main "$@"
