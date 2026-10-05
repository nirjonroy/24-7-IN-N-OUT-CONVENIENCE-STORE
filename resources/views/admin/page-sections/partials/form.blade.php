@php
  $settingsValue = old('settings', is_array($section->settings) ? json_encode($section->settings, JSON_PRETTY_PRINT) : $section->settings);
@endphp
<div class="card-body">
  <h5>Section Information</h5>
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Section Key <span class="text-danger">*</span></label><input name="section_key" value="{{ old('section_key', $section->section_key) }}" class="form-control @error('section_key') is-invalid @enderror" required />@error('section_key')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Section Type <span class="text-danger">*</span></label><select name="section_type" class="form-select">@foreach($sectionTypes as $value => $label)<option value="{{ $value }}" @selected(old('section_type', $section->section_type ?: 'custom') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-4 mb-3"><label class="form-label">Section Label</label><input name="section_label" value="{{ old('section_label', $section->section_label) }}" class="form-control" /></div>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Title</label><input name="title" value="{{ old('title', $section->title) }}" class="form-control" /></div>
    <div class="col-md-6 mb-3"><label class="form-label">Subtitle</label><input name="subtitle" value="{{ old('subtitle', $section->subtitle) }}" class="form-control" /></div>
  </div>
  <div class="mb-3"><label class="form-label">Content</label><textarea name="content" rows="4" class="form-control">{{ old('content', $section->content) }}</textarea></div>
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Image</label><input name="image" value="{{ old('image', $section->image) }}" class="form-control" /></div>
    <div class="col-md-4 mb-3"><label class="form-label">Image Alt</label><input name="image_alt" value="{{ old('image_alt', $section->image_alt) }}" class="form-control" /></div>
    <div class="col-md-4 mb-3"><label class="form-label">Background Image</label><input name="background_image" value="{{ old('background_image', $section->background_image) }}" class="form-control" /></div>
  </div>
  <div class="form-text mb-3">Image path fields above remain as legacy / external URL fallbacks.</div>
  @include('admin.components.media-picker', ['model' => $section, 'collection' => 'image', 'label' => 'Section Image Media Library', 'fieldName' => 'image_media_id', 'altFieldName' => 'image_alt_override'])
  @include('admin.components.media-picker', ['model' => $section, 'collection' => 'background_image', 'label' => 'Background Image Media Library', 'fieldName' => 'background_image_media_id', 'altFieldName' => 'background_image_alt_override'])
  <div class="row">
    <div class="col-md-3 mb-3"><label class="form-label">Primary Button Label</label><input name="primary_button_label" value="{{ old('primary_button_label', $section->primary_button_label) }}" class="form-control" /></div>
    <div class="col-md-3 mb-3"><label class="form-label">Primary Button URL</label><input name="primary_button_url" value="{{ old('primary_button_url', $section->primary_button_url) }}" class="form-control" /></div>
    <div class="col-md-3 mb-3"><label class="form-label">Secondary Button Label</label><input name="secondary_button_label" value="{{ old('secondary_button_label', $section->secondary_button_label) }}" class="form-control" /></div>
    <div class="col-md-3 mb-3"><label class="form-label">Secondary Button URL</label><input name="secondary_button_url" value="{{ old('secondary_button_url', $section->secondary_button_url) }}" class="form-control" /></div>
  </div>
  <div class="row">
    <div class="col-md-3 mb-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $section->sort_order ?? 0) }}" class="form-control" /></div>
    <div class="col-md-3 mb-3 d-flex align-items-end"><div class="form-check form-switch mb-2"><input type="hidden" name="is_active" value="0" /><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $section->is_active) ? 'checked' : '' }} /><label class="form-check-label" for="is_active">Active</label></div></div>
    <div class="col-md-6 mb-3"><label class="form-label">Settings JSON</label><textarea name="settings" rows="2" class="form-control @error('settings') is-invalid @enderror">{{ $settingsValue }}</textarea>@error('settings')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
  </div>
  @include('admin.partials.seo-fields', ['model' => $section, 'mediaModel' => $section, 'mediaCollections' => ['meta_image']])
</div>
<div class="card-footer d-flex gap-2"><button class="btn btn-primary">Save Section</button><a href="{{ route('admin.pages.sections.index', $page) }}" class="btn btn-secondary">Cancel</a></div>
