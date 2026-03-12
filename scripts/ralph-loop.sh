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
#   AUTO_COMMIT_CHANGES=0 Disable automatic checkpoint commits before branch changes and after each phase

set -euo pipefail

# ─── Config ───────────────────────────────────────────────────────────────────

MAX_PHASES="${MAX_PHASES:-20}"
START_FROM_PHASE="${START_FROM_PHASE:-}"
OPENCODE_MODEL="${OPENCODE_MODEL:-}"
OPENCODE_BIN="${OPENCODE_BIN:-opencode}"
AUTO_COMMIT_CHANGES="${AUTO_COMMIT_CHANGES:-1}"

PROJECT_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SPECS_DIR="$PROJECT_ROOT/specs"
LOG_DIR="$PROJECT_ROOT/logs/ralph"

PHASES_COMPLETED=0
declare -a OPENCODE_FLAGS=()
LAST_PHASE_FILE=""
LAST_PHASE_SIGNATURE=""

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

git_worktree_has_changes() {
    [[ -n "$(git -C "$PROJECT_ROOT" status --porcelain --untracked-files=normal)" ]]
}

is_phase_feature_branch() {
    local branch_name="$1"
    [[ "$branch_name" == feature/phase-* ]]
}

phase_progress_signature() {
    local plan_file="$1"
    shasum -a 256 "$plan_file" | awk '{print $1}'
}

auto_commit_changes() {
    local reason="$1"
    local current_branch
    current_branch=$(git -C "$PROJECT_ROOT" branch --show-current)

    if [[ "$AUTO_COMMIT_CHANGES" != "1" ]]; then
        return 0
    fi

    if ! git_worktree_has_changes; then
        return 0
    fi

    log "Auto-committing local changes on '${current_branch:-detached}' ($reason)."

    git -C "$PROJECT_ROOT" add -A

    if git -C "$PROJECT_ROOT" commit -m "chore(ralph-loop): checkpoint before continuing [$reason]"; then
        log "Checkpoint commit created successfully."
        return 0
    fi

    log "FATAL: unable to create checkpoint commit. Configure git user.name/user.email or disable auto-commit with AUTO_COMMIT_CHANGES=0."
    return 1
}

prepare_git_workspace() {
    local current_branch
    current_branch=$(git -C "$PROJECT_ROOT" branch --show-current)

    log "  Current branch: ${current_branch:-detached}"

    auto_commit_changes "startup checkpoint" || return 1

    if git_worktree_has_changes; then
        log "Local changes detected on '${current_branch:-detached}'. Skipping checkout/pull and resuming on the current branch."
        return 0
    fi

    if is_phase_feature_branch "$current_branch"; then
        log "Already on Ralph phase branch '$current_branch'. Resuming there."
        return 0
    fi

    if ! git -C "$PROJECT_ROOT" show-ref --verify --quiet refs/heads/develop; then
        log "Branch 'develop' does not exist. Creating it from '$current_branch'..."
        git -C "$PROJECT_ROOT" checkout -b develop
    elif [[ "$current_branch" != "develop" ]]; then
        log "Clean worktree on '$current_branch'. Switching to develop..."
        git -C "$PROJECT_ROOT" checkout develop
    fi

    if git -C "$PROJECT_ROOT" remote get-url origin &>/dev/null; then
        log "Pulling latest from origin/develop..."
        git -C "$PROJECT_ROOT" pull --ff-only origin develop || log "Note: origin/develop may not exist yet, may require authentication, or is not a fast-forward."
    fi
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
    OPENCODE_FLAGS=()

    if [[ -n "$OPENCODE_MODEL" ]]; then
        OPENCODE_FLAGS+=("--model" "$OPENCODE_MODEL")
    fi
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
    build_opencode_flags

    local exit_code=0
    set +e
    (
        cd "$PROJECT_ROOT"
        if (( ${#OPENCODE_FLAGS[@]} > 0 )); then
            "$OPENCODE_BIN" run \
                --command "$command_name" \
                "${OPENCODE_FLAGS[@]}" \
                "$relative_plan_file"
        else
            "$OPENCODE_BIN" run \
                --command "$command_name" \
                "$relative_plan_file"
        fi
    ) 2>&1 | tee "$step_log"
    exit_code=${PIPESTATUS[0]}
    set -e

    if [[ $exit_code -ne 0 ]]; then
        log "  [$step_name] FAILED (exit $exit_code). See $step_log for details."
        return "$exit_code"
    fi

    log "  [$step_name] Completed successfully."
    return 0
}

# ─── Main Loop ────────────────────────────────────────────────────────────────

main() {
    mkdir -p "$LOG_DIR"

    log_separator
    log "Ralph Loop starting ($OPENCODE_BIN $( "$OPENCODE_BIN" --version 2>/dev/null || echo 'unknown' ))"
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

    # ── Prepare the git workspace without clobbering in-progress work ─────────

    prepare_git_workspace

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

        local phase_signature
        phase_signature=$(phase_progress_signature "$plan_file")

        if [[ "$plan_file" == "$LAST_PHASE_FILE" && "$phase_signature" == "$LAST_PHASE_SIGNATURE" ]]; then
            log "FATAL: phase $phase_num was selected again without any plan-file progress. Stopping to avoid a loop."
            log "  Plan file: $plan_file"
            exit 1
        fi

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
        if run_opencode_step "execute-phase" "execute-phase" "$plan_file" "$phase_log"; then
            :
        else
            local execute_exit_code=$?
            log "FATAL: execute-phase failed for phase $phase_num. Stopping loop."
            exit "$execute_exit_code"
        fi

        # 2. Verify phase
        if run_opencode_step "verify-phase" "verify-phase" "$plan_file" "$phase_log"; then
            :
        else
            local verify_exit_code=$?
            log "FATAL: verify-phase failed for phase $phase_num. Stopping loop."
            exit "$verify_exit_code"
        fi

        PHASES_COMPLETED=$(( PHASES_COMPLETED + 1 ))
        phase_count=$(( phase_count + 1 ))

        auto_commit_changes "after phase $phase_num" || exit 1

        LAST_PHASE_FILE="$plan_file"
        LAST_PHASE_SIGNATURE=$(phase_progress_signature "$plan_file")

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
