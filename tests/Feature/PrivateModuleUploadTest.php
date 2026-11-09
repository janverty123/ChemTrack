<?php

namespace Tests\Feature;

use App\Filament\Resources\Programmes\Pages\CreateProgramme;
use App\Filament\Resources\Programmes\Pages\EditProgramme;
use App\Models\Programme;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PrivateModuleUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_cannot_sign_or_attach_another_teachers_pdf_by_forging_a_path(): void
    {
        Storage::fake('local');
        $teacher = User::factory()->create(['role' => 'teacher']);
        $other = User::factory()->create(['role' => 'teacher']);
        $programme = Programme::create(['teacher_id' => $teacher->id, 'title' => 'Own module']);
        $foreign = Programme::create(['teacher_id' => $other->id, 'title' => 'Other module']);
        Storage::disk('local')->put('modules/own.pdf', "%PDF-1.4\n% own synthetic fixture");
        Storage::disk('local')->put('modules/foreign.pdf', "%PDF-1.4\n% foreign synthetic fixture");
        $lesson = $programme->activities()->create(['title' => 'Own reading', 'type' => 'lesson', 'document_path' => 'modules/own.pdf']);
        $foreign->activities()->create(['title' => 'Foreign reading', 'type' => 'lesson', 'document_path' => 'modules/foreign.pdf']);
        $this->actingAs($teacher);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $component = Livewire::test(EditProgramme::class, ['record' => $programme->id]);
        $field = collect($component->instance()->form->getFlatFields(withHidden: true))->first(fn ($field) => $field instanceof FileUpload);
        $this->assertNotNull($field);
        $this->assertNotNull(collect($field->getUploadedFiles())->first(), 'Own persisted PDF remains usable');
        $path = $field->getStatePath();
        $component->set($path, ['forged' => 'modules/foreign.pdf']);
        $field = collect($component->instance()->form->getFlatFields(withHidden: true))->first(fn ($field) => $field instanceof FileUpload);
        $this->assertNull(collect($field->getUploadedFiles())->first(), 'Foreign PDF metadata/signed URL must not be generated');
        $component->call('save')->assertHasFormErrors();
        $this->assertSame('modules/own.pdf', $lesson->fresh()->document_path);
    }

    public function test_teacher_can_upload_a_new_private_pdf(): void
    {
        Storage::fake('local');
        $teacher = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($teacher);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $file = UploadedFile::fake()->create('sample-module.pdf', 5, 'application/pdf');
        Livewire::test(CreateProgramme::class)->fillForm([
            'title' => 'PDF module', 'published' => false,
            'activities' => [['title' => 'Read the module', 'type' => 'lesson', 'cycle' => 1, 'document_path' => [$file]]],
        ])->call('create')->assertHasNoFormErrors();
        $path = Programme::firstOrFail()->activities->first()->document_path;
        $this->assertStringStartsWith('modules/', $path);
        Storage::disk('local')->assertExists($path);
    }
}
