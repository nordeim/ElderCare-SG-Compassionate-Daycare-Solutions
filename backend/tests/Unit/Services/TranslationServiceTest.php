<?php

namespace Tests\Unit\Services;

use App\Models\ContentTranslation;
use App\Models\Center;
use App\Models\User;
use App\Services\Translation\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TranslationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(TranslationService::class);
    }

    public function test_create_translation_and_retrieve()
    {
        $center = Center::factory()->create();

        $translation = $this->service->createTranslation(
            Center::class,
            $center->id,
            'zh',
            'name',
            '中心名稱',
            null
        );

        $this->assertInstanceOf(ContentTranslation::class, $translation);
        $this->assertEquals('zh', $translation->locale);

        $found = $this->service->getTranslation(Center::class, $center->id, 'zh', 'name');
        $this->assertNotNull($found);
        $this->assertEquals('中心名稱', $found->value);
    }

    public function test_update_translation()
    {
        $t = ContentTranslation::factory()->create(['translation_status' => 'draft']);

        $updated = $this->service->updateTranslation($t->id, '新的翻譯');

        $this->assertEquals('新的翻譯', $updated->value);
    }

    public function test_mark_translated_and_review_and_publish_flow()
    {
        $t = ContentTranslation::factory()->create(['translation_status' => 'draft', 'translated_by' => null]);

        $translator = User::factory()->create();
        $reviewer = User::factory()->create();

        $translated = $this->service->markTranslated($t->id, $translator->id);
        $this->assertEquals('translated', $translated->translation_status);
        $this->assertEquals($translator->id, $translated->translated_by);

        $reviewed = $this->service->markReviewed($translated->id, $reviewer->id);
        $this->assertEquals('reviewed', $reviewed->translation_status);
        $this->assertEquals($reviewer->id, $reviewed->reviewed_by);

        $published = $this->service->publish($reviewed->id);
        $this->assertEquals('published', $published->translation_status);
    }

    public function test_invalid_state_transitions_throw()
    {
        $this->expectException(\RuntimeException::class);

        $t = ContentTranslation::factory()->create(['translation_status' => 'translated']);

        $user = User::factory()->create();

        // Trying to markTranslated when already 'translated' should fail
        $this->service->markTranslated($t->id, $user->id);
    }

    public function test_get_pending_translations_and_coverage()
    {
        $center = Center::factory()->create();

        ContentTranslation::factory()->create([
            'translatable_type' => Center::class,
            'translatable_id' => $center->id,
            'locale' => 'zh',
            'field' => 'name',
            'translation_status' => 'draft',
        ]);

        $pending = $this->service->getPendingTranslations('zh', 'draft');
        $this->assertNotEmpty($pending);

        $coverage = $this->service->getTranslationCoverage(Center::class, $center->id);
        $this->assertIsArray($coverage);
        $this->assertArrayHasKey('zh', $coverage);
    }
}
