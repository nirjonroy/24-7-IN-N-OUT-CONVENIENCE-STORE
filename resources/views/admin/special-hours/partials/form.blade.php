<div class="card-body">
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Date <span class="text-danger">*</span></label><input type="date" name="date" value="{{ old('date', $specialHour->date?->format('Y-m-d')) }}" class="form-control @error('date') is-invalid @enderror" required />@error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Open Time</label><input type="time" name="opens_at" value="{{ old('opens_at', $specialHour->opens_at ? substr($specialHour->opens_at,0,5) : '') }}" class="form-control" /></div>
    <div class="col-md-4 mb-3"><label class="form-label">Close Time</label><input type="time" name="closes_at" value="{{ old('closes_at', $specialHour->closes_at ? substr($specialHour->closes_at,0,5) : '') }}" class="form-control" /></div>
  </div>
  <div class="mb-3"><label class="form-label">Note</label><input name="note" value="{{ old('note', $specialHour->note) }}" class="form-control" /></div>
  <div class="form-check form-switch"><input type="hidden" name="is_closed" value="0" /><input class="form-check-input" type="checkbox" name="is_closed" value="1" id="is_closed" @checked(old('is_closed', $specialHour->is_closed)) /><label class="form-check-label" for="is_closed">Closed</label></div>
</div>
<div class="card-footer d-flex gap-2"><button class="btn btn-primary">Save Special Hours</button><a href="{{ route('admin.locations.special-hours.index', $location) }}" class="btn btn-secondary">Cancel</a></div>
