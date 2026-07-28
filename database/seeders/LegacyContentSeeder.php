<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * One-off migration of real content from the legacy CakePHP database
 * (imported from db/00-structure.sql + db/02-data-prod.sql into a
 * local `cessa_legacy` database, see config/database.php 'legacy' connection)
 * into the new Laravel schema. Run manually with:
 *   php artisan db:seed --class=LegacyContentSeeder
 */
class LegacyContentSeeder extends Seeder
{
    public function run(): void
    {
        $legacy = DB::connection('legacy');

        $this->migrateCategories($legacy);
        $this->migrateContents($legacy);
        $this->migrateNews($legacy);
        $this->migrateFaqs($legacy);
        $this->migratePublications($legacy);
        $this->migrateDocuments($legacy);
        $this->migrateScheduledOutages($legacy);
        $this->migrateVideos($legacy);
        $this->migrateImages($legacy);
    }

    protected function pub(?string $flag): string
    {
        return $flag === 'Y' ? 'S' : 'N';
    }

    protected function migrateCategories($legacy): void
    {
        foreach ($legacy->table('categories')->get() as $row) {
            DB::table('categories')->updateOrInsert(['id' => $row->id], [
                'title' => $row->title,
                'alias' => $row->alias,
                'description' => $row->description,
                'published' => $this->pub($row->published),
                'created_by' => $row->created_by,
                'modified_by' => $row->modified_by,
                'created_at' => $row->created,
                'updated_at' => $row->modified ?? $row->created,
            ]);
        }
    }

    protected function migrateContents($legacy): void
    {
        foreach ($legacy->table('contents')->get() as $row) {
            DB::table('contents')->updateOrInsert(['id' => $row->id], [
                'category_id' => $row->category_id,
                'title' => $row->title,
                'alias' => $row->alias,
                'summary' => $row->summary,
                'full_text' => $row->full_text,
                'hits' => $row->hits ?? 0,
                'position' => $row->position ?? 0,
                'published' => $this->pub($row->published),
                'created_by' => $row->created_by,
                'modified_by' => $row->modified_by,
                'created_at' => $row->created,
                'updated_at' => $row->modified ?? $row->created,
            ]);
        }
    }

    protected function migrateNews($legacy): void
    {
        foreach ($legacy->table('news')->get() as $row) {
            DB::table('news')->updateOrInsert(['id' => $row->id], [
                'title' => $row->title,
                'alias' => $row->alias,
                'summary' => $row->summary,
                'full_text' => $row->full_text,
                'tags' => $row->tags,
                'hits' => $row->hits ?? 0,
                'popup' => false,
                'published' => $this->pub($row->published),
                'created_by' => $row->created_by,
                'modified_by' => $row->modified_by,
                'created_at' => $row->created,
                'updated_at' => $row->modified ?? $row->created,
            ]);
        }
    }

    protected function migrateFaqs($legacy): void
    {
        foreach ($legacy->table('faqs')->get() as $row) {
            DB::table('faqs')->updateOrInsert(['id' => $row->id], [
                'question' => $row->question,
                'answer' => $row->answer,
                'position' => $row->position ?? 0,
                'published' => $this->pub($row->published),
                'created_by' => $row->created_by,
                'modified_by' => $row->modified_by,
                'created_at' => $row->created,
                'updated_at' => $row->modified ?? $row->created,
            ]);
        }
    }

    protected function migratePublications($legacy): void
    {
        foreach ($legacy->table('publications')->get() as $row) {
            DB::table('publications')->updateOrInsert(['id' => $row->id], [
                'title' => $row->title,
                'description' => $row->description,
                'expired_date' => $row->expired_date,
                'type' => $row->type,
                'published' => $this->pub($row->published),
                'created_by' => $row->created_by,
                'modified_by' => $row->modified_by,
                'created_at' => $row->created,
                'updated_at' => $row->modified ?? $row->created,
            ]);
        }
    }

    protected function migrateDocuments($legacy): void
    {
        foreach ($legacy->table('documents')->get() as $row) {
            DB::table('documents')->updateOrInsert(['id' => $row->id], [
                'title' => $row->title,
                'url' => $row->url,
                'position' => $row->position ?? 0,
                'publication_id' => $row->publication_id,
                'published' => $this->pub($row->published),
                'created_by' => $row->created_by,
                'modified_by' => $row->modified_by,
                'created_at' => $row->created,
                'updated_at' => $row->modified ?? $row->created,
            ]);
        }
    }

    protected function migrateScheduledOutages($legacy): void
    {
        foreach ($legacy->table('scheduled_outages')->get() as $row) {
            DB::table('scheduled_outages')->updateOrInsert(['id' => $row->id], [
                'reason' => trim($row->reason),
                'location' => $row->location,
                'execution_date' => $row->execution_date,
                'start_time' => $row->start_time,
                'finish_time' => $row->finish_time,
                'published' => $this->pub($row->published),
                'created_by' => $row->created_by,
                'modified_by' => $row->modified_by,
                'created_at' => $row->created,
                'updated_at' => $row->modified ?? $row->created,
            ]);
        }
    }

    protected function migrateVideos($legacy): void
    {
        foreach ($legacy->table('videos')->get() as $row) {
            DB::table('videos')->updateOrInsert(['id' => $row->id], [
                'title' => $row->title,
                'description' => $row->description,
                'url' => $row->url,
                'position' => $row->position ?? 0,
                'published' => $this->pub($row->published),
                'created_by' => $row->created_by,
                'modified_by' => $row->modified_by,
                'created_at' => $row->created,
                'updated_at' => $row->modified ?? $row->created,
            ]);
        }
    }

    protected function migrateImages($legacy): void
    {
        foreach ($legacy->table('images')->get() as $row) {
            DB::table('images')->updateOrInsert(['id' => $row->id], [
                'title' => $row->title,
                'url' => $row->url,
                'position' => $row->position ?? 0,
                'published' => $this->pub($row->published),
                'created_by' => $row->created_by,
                'modified_by' => $row->modified_by,
                'created_at' => $row->created,
                'updated_at' => $row->modified ?? $row->created,
            ]);
        }
    }
}
