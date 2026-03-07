<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dev;

use App\Contracts\Previewable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

final class MailPreviewController
{
    /**
     * Display a listing of all previewable mailables.
     */
    public function index(): View
    {
        $mailables = $this->discoverMailables();

        return view('dev.mail-preview-index', [
            'mailables' => $mailables,
        ]);
    }

    /**
     * Render a specific mailable preview.
     */
    public function show(string $mailable): Response
    {
        $mailables = $this->discoverMailables();
        $class = $mailables[$mailable] ?? null;

        if ($class === null) {
            abort(404, "Mailable [{$mailable}] not found.");
        }

        /** @var Mailable $instance */
        $instance = $class::preview();

        return new Response($instance->render());
    }

    /**
     * Discover all classes implementing the Previewable interface under app/Mail/.
     *
     * @return array<string, class-string<Previewable>>
     */
    private function discoverMailables(): array
    {
        return once(function (): array {
            $mailPath = app_path('Mail');
            $mailables = [];

            if (! File::isDirectory($mailPath)) {
                return $mailables;
            }

            foreach (File::allFiles($mailPath) as $file) {
                $relativePath = $file->getRelativePathname();

                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $class = 'App\\Mail\\'.str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    $relativePath,
                );

                if (! class_exists($class)) {
                    continue;
                }

                $reflection = new ReflectionClass($class);

                if ($reflection->isAbstract() || $reflection->isInterface()) {
                    continue;
                }

                if (! $reflection->implementsInterface(Previewable::class)) {
                    continue;
                }

                $slug = Str::kebab(class_basename($class));
                $mailables[$slug] = $class;
            }

            ksort($mailables);

            return $mailables;
        });
    }
}
