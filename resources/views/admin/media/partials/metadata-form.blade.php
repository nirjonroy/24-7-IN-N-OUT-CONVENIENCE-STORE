<div class="row">
  <div class="col-md-6 mb-3"><label class="form-label">Title</label><input name="title" value="{{ old('title', $media->title) }}" class="form-control"></div>
  <div class="col-md-6 mb-3"><label class="form-label">Alt Text</label><input name="alt_text" value="{{ old('alt_text', $media->alt_text) }}" class="form-control"><div class="form-text">Describe the image only when it adds meaning.</div></div>
</div>
<div class="mb-3"><label class="form-label">Caption</label><textarea name="caption" rows="3" class="form-control">{{ old('caption', $media->caption) }}</textarea></div>
<div class="row">
  <div class="col-md-6 mb-3"><label class="form-label">Credit</label><input name="credit" value="{{ old('credit', $media->credit) }}" class="form-control"></div>
  <div class="col-md-6 mb-3"><label class="form-label">Source URL</label><input name="source_url" value="{{ old('source_url', $media->source_url) }}" class="form-control @error('source_url') is-invalid @enderror">@error('source_url')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
</div>
