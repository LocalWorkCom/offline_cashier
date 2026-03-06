@if($cities)
<option value="" disabled selected>@lang('country.ChooseCity')</option>
@foreach ($cities as $city)
<option value="{{ $city->id }}">{{ $city->name_ar . " | " . $city->name_en}}</option>
@endforeach
@endif