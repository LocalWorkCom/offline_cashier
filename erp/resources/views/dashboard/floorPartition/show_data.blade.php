@if($floorPartitions)
        <option value="" disabled selected>@lang('floor.ChoosePartition')</option>
        @foreach ($floorPartitions as $partition)
            <option value="{{ $partition->id }}">{{ $partition->name_ar . " | " . $partition->name_en}}</option>
        @endforeach
@endif