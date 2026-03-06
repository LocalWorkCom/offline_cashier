@if($leaveSettings)
<option value="" disabled selected>@lang('floor.ChoosePartition')</option>
@foreach ($leaveSettings as $leaveSetting)
<option value="{{ $leaveSetting->id }}" data-dayCount="{{ $leaveSetting->day_count }}">{{ $leaveSetting->leaveTypes ? $leaveSetting->leaveTypes->name_site : ""}}</option>
@endforeach
@endif