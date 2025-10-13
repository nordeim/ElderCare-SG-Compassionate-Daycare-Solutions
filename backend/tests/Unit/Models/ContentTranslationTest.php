<?php

namespace Tests\Unit\Models;

use App\Models\ContentTranslation;
use App\Models\Center;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTranslationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_polymorphic_translatable_relationship()
    {
        $center = Center::factory()->create();
        $translation = ContentTranslation::factory()->create([
            'translatable_type' => Center::class,
            'translatable_id' => $center->id,
        ]);

        $this->assertInstanceOf(Center::class, $translation->translatable);
        $this->assertEquals($center->id, $translation->translatable->id);
    }

    /** @test */
    public function it_has_supported_locales()
    {
        $locales = ['en', 'zh', 'ms', 'ta'];

        foreach ($locales as $locale) {
            $translation = ContentTranslation::factory()->create(['locale' => $locale]);
            $this->assertEquals($locale, $translation->locale);
        }
    }

    /** @test */
    public function it_has_translation_workflow_statuses()
    {
        $statuses = ['draft', 'translated', 'reviewed', 'published'];

        foreach ($statuses as $status) {
            $translation = ContentTranslation::factory()->create(['translation_status' => $status]);
            $this->assertEquals($status, $translation->translation_status);
        }
    }

    /** @test */
    public function it_belongs_to_translator()
    {
        $translator = User::factory()->create(['role' => 'admin']);
        $translation = ContentTranslation::factory()->create([
            'translated_by' => $translator->id,
        ]);

        $this->assertInstanceOf(User::class, $translation->translator);
        $this->assertEquals($translator->id, $translation->translator->id);
    }

    /** @test */
    public function it_belongs_to_reviewer()
    {
        $reviewer = User::factory()->create(['role' => 'admin']);
        $translation = ContentTranslation::factory()->create([
            'reviewed_by' => $reviewer->id,
            'translation_status' => 'reviewed',
        ]);

        $this->assertInstanceOf(User::class, $translation->reviewer);
        $this->assertEquals($reviewer->id, $translation->reviewer->id);
    }

    /** @test */
    public function it_stores_field_and_value()
    {
        $translation = ContentTranslation::factory()->create([
            'field' => 'name',
            'value' => '金色年华护理中心',
        ]);

        $this->assertEquals('name', $translation->field);
        $this->assertEquals('金色年华护理中心', $translation->value);
    }

    /** @test */
    public function it_enforces_unique_constraint()
    {
        $center = Center::factory()->create();

        $translation1 = ContentTranslation::factory()->create([
            'translatable_type' => Center::class,
            'translatable_id' => $center->id,
            'locale' => 'zh',
            'field' => 'name',
        ]);

        $this->assertDatabaseHas('content_translations', [
            'translatable_type' => Center::class,
            'translatable_id' => $center->id,
            'locale' => 'zh',
            'field' => 'name',
        ]);
    }
}
