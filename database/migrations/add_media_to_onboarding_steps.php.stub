<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Media on a step: an image to show, or a video to watch.
 *
 * Uploads keep the disk they landed on (S3, R2, local) alongside the path, so a
 * private bucket can be signed at render time; everything else is a URL. Watch
 * time itself is not stored here — it belongs to the subject, and lives in the
 * step progress meta.
 *
 * Portable across PostgreSQL and MySQL/MariaDB.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->table('steps'), function (Blueprint $table): void {
            $table->string('media_type')->default('none');
            $table->string('media_source')->nullable();
            $table->string('media_disk')->nullable();
            $table->string('media_path', 2048)->nullable();
            $table->string('media_url', 2048)->nullable();
            $table->json('media_caption')->nullable();
            $table->string('modal_position')->nullable();

            // Percentage of the video that counts as watched. Nobody sits
            // through the credits, so the default lands short of the end.
            $table->unsignedTinyInteger('video_completion_threshold')->default(90);
        });
    }

    public function down(): void
    {
        Schema::table($this->table('steps'), function (Blueprint $table): void {
            $table->dropColumn([
                'media_type',
                'media_source',
                'media_disk',
                'media_path',
                'media_url',
                'media_caption',
                'modal_position',
                'video_completion_threshold',
            ]);
        });
    }

    private function table(string $key): string
    {
        return config("filament-onboarding.tables.{$key}", "onboarding_{$key}");
    }
};
