@if($regions)
<option value="" disabled selected>@lang('country.ChooseRegion')</option>
@foreach ($regions as $region)
<option value="{{ $region->id }}">{{ $region->name_ar . " | " . $region->name_en}}</option>
@endforeach
@endif