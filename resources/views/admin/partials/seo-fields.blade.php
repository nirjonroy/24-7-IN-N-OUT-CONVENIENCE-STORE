<hr />
<h5>SEO Information</h5>
<div class="row">
  <div class="col-md-4 mb-3">
    <label for="page_name" class="form-label">Page Name</label>
    <input type="text" name="page_name" id="page_name" value="{{ old('page_name', $model->page_name ?? '') }}" class="form-control @error('page_name') is-invalid @enderror" />
    @error('page_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-4 mb-3">
    <label for="seo_title" class="form-label">SEO Title</label>
    <input type="text" name="seo_title" id="seo_title" value="{{ old('seo_title', $model->seo_title ?? '') }}" class="form-control @error('seo_title') is-invalid @enderror" />
    <div class="form-text">Recommended concise title.</div>
    @error('seo_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-4 mb-3">
    <label for="meta_title" class="form-label">Meta Title</label>
    <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $model->meta_title ?? '') }}" class="form-control @error('meta_title') is-invalid @enderror" />
    @error('meta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</div>
<div class="row">
  <div class="col-md-6 mb-3">
    <label for="seo_description" class="form-label">SEO Description</label>
    <textarea name="seo_description" id="seo_description" rows="3" class="form-control @error('seo_description') is-invalid @enderror">{{ old('seo_description', $model->seo_description ?? '') }}</textarea>
    @error('seo_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-6 mb-3">
    <label for="meta_description" class="form-label">Meta Description</label>
    <textarea name="meta_description" id="meta_description" rows="3" class="form-control @error('meta_description') is-invalid @enderror">{{ old('meta_description', $model->meta_description ?? '') }}</textarea>
    <div class="form-text">Recommended concise search description.</div>
    @error('meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</div>
<div class="row">
  <div class="col-md-4 mb-3">
    <label for="meta_image" class="form-label">Meta Image</label>
    <input type="text" name="meta_image" id="meta_image" value="{{ old('meta_image', $model->meta_image ?? '') }}" class="form-control @error('meta_image') is-invalid @enderror" />
    <div class="form-text">Legacy / external image URL fallback.</div>
    @error('meta_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-4 mb-3">
    <label for="author" class="form-label">Author</label>
    <input type="text" name="author" id="author" value="{{ old('author', $model->author ?? '') }}" class="form-control @error('author') is-invalid @enderror" />
    @error('author')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-4 mb-3">
    <label for="publisher" class="form-label">Publisher</label>
    <input type="text" name="publisher" id="publisher" value="{{ old('publisher', $model->publisher ?? '') }}" class="form-control @error('publisher') is-invalid @enderror" />
    @error('publisher')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</div>
@if(isset($mediaModel) && in_array('meta_image', $mediaCollections ?? [], true))
  @include('admin.components.media-picker', [
    'model' => $mediaModel,
    'collection' => 'meta_image',
    'label' => 'Meta Image Media Library',
    'fieldName' => 'meta_image_media_id',
    'altFieldName' => 'meta_image_alt_override',
  ])
@endif
<div class="row">
  <div class="col-md-4 mb-3">
    <label for="copyright" class="form-label">Copyright</label>
    <input type="text" name="copyright" id="copyright" value="{{ old('copyright', $model->copyright ?? '') }}" class="form-control @error('copyright') is-invalid @enderror" />
    @error('copyright')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-4 mb-3">
    <label for="site_name" class="form-label">Site Name</label>
    <input type="text" name="site_name" id="site_name" value="{{ old('site_name', $model->site_name ?? '') }}" class="form-control @error('site_name') is-invalid @enderror" />
    @error('site_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-4 mb-3">
    <label for="keywords" class="form-label">Keywords</label>
    <textarea name="keywords" id="keywords" rows="1" class="form-control @error('keywords') is-invalid @enderror">{{ old('keywords', $model->keywords ?? '') }}</textarea>
    @error('keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</div>
