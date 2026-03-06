@extends('layouts.master')

@section('styles')
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <style>
        /* Ensure content is visible */
        .text-center {
            text-align: center;
            display: block;
            margin: 20px 0;
        }

        .country-selector {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background: #2b89e7;
            border-radius: 5px;
        }

        /* Remove default weekend styling */
        .fc-day-sun,
        .fc-day-sat {
            background-color: inherit !important;
        }

        /* Custom weekend class */
        .custom-weekend {
            background-color: #3b5dce !important;
        }

        /* Calendar container */
        .calendar-container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background: rgb(0, 0, 0);
            color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(218, 11, 11, 0.274);
        }

        /* Day header styling */
        .fc .fc-col-header-cell {
            background-color: #7700ff !important;
            color: white !important;
            font-weight: bold;
            padding: 10px 0;
        }

        /* Today button styling to match current day */
        .fc .fc-today-button {
            background-color: #3b5dce !important;
            border-color: #3b5dce !important;
            color: white !important;
        }

        /* Today's date cell styling */
        .fc .fc-daygrid-day.fc-day-today {
            background-color: rgba(247, 247, 247, 0.2) !important;
        }
    </style>
@endsection

@section('content')
    <div class="calendar-header">

        <form method="GET" class="country-selector">
            <h6 class="text-center">Public Holidays Calendar</h6>
            <label for="country" style="margin-right: 10px;">Select Country:</label>
            <select name="country" id="country" onchange="this.form.submit()"
                style="padding: 5px; border-radius: 4px; border: 1px solid #dddddd80;">
                <option value="eg" {{ $selectedCountry == 'eg' ? 'selected' : '' }}>Egypt</option>
                <option value="ae" {{ $selectedCountry == 'ae' ? 'selected' : '' }}>Emirates</option>
                <option value="saudiarabian" {{ $selectedCountry == 'saudiarabian' ? 'selected' : '' }}>Saudi Arabia
                </option>
                <option value="kw" {{ $selectedCountry == 'kw' ? 'selected' : '' }}>Kuwait</option>
                <option value="us" {{ $selectedCountry == 'us' ? 'selected' : '' }}>United States</option>
                <option value="gb" {{ $selectedCountry == 'gb' ? 'selected' : '' }}>United Kingdom</option>
                <option value="ca" {{ $selectedCountry == 'ca' ? 'selected' : '' }}>canadian</option>
                <option value="de" {{ $selectedCountry == 'de' ? 'selected' : '' }}>german</option>
                <option value="fr" {{ $selectedCountry == 'fr' ? 'selected' : '' }}>french</option>
                <option value="it" {{ $selectedCountry == 'it' ? 'selected' : '' }}>italian</option>
                <option value="jp" {{ $selectedCountry == 'jp' ? 'selected' : '' }}>japanese</option>
                <option value="ind" {{ $selectedCountry == 'ind' ? 'selected' : '' }}>indian</option>
                <option value="in" {{ $selectedCountry == 'in' ? 'selected' : '' }}>indonesian</option>
                <option value="kz" {{ $selectedCountry == 'kz' ? 'selected' : '' }}>Kazakhstan</option>
                <option value="ir" {{ $selectedCountry == 'ir' ? 'selected' : '' }}>Iran</option>
                <option value="iq" {{ $selectedCountry == 'iq' ? 'selected' : '' }}>Iraq</option>
                <option value="ly" {{ $selectedCountry == 'ly' ? 'selected' : '' }}>Libya</option>
                <option value="Morocco" {{ $selectedCountry == 'Morocco' ? 'selected' : '' }}>Morocco</option>
                <option value="Syria" {{ $selectedCountry == 'Syria' ? 'selected' : '' }}>Syria</option>
                <option value="Turkey" {{ $selectedCountry == 'Turkey' ? 'selected' : '' }}>Turkey</option>
                <option value="Tunisia" {{ $selectedCountry == 'Tunisia' ? 'selected' : '' }}>Tunisia</option>
                <option value="Qatar" {{ $selectedCountry == 'Qatar' ? 'selected' : '' }}>Qatar</option>
                <option value="Sudan" {{ $selectedCountry == 'Sudan' ? 'selected' : '' }}>Sudan</option>
                <option value="ss" {{ $selectedCountry == 'ss' ? 'selected' : '' }}>South Sudan</option>
                <option value="malaysia" {{ $selectedCountry == 'malaysia' ? 'selected' : '' }}>Malaysia</option>

                <option value="Mozambique" {{ $selectedCountry == 'Mozambique' ? 'selected' : '' }}>Mozambique</option>
                <option value="North Korea" {{ $selectedCountry == 'North Korea' ? 'selected' : '' }}>North Korea</option>
                <option value="Portugal" {{ $selectedCountry == 'Portugal' ? 'selected' : '' }}>Portugal</option>
                <option value="Russia" {{ $selectedCountry == 'Russia' ? 'selected' : '' }}>Russia</option>
                <option value="South Africa" {{ $selectedCountry == 'South Africa' ? 'selected' : '' }}>South Africa
                </option>
                <option value="South Korea" {{ $selectedCountry == 'South Korea' ? 'selected' : '' }}>South Korea</option>
                <option value="South Spain" {{ $selectedCountry == 'South Spain' ? 'selected' : '' }}>South Spain</option>
                <option value="South Tajikistan" {{ $selectedCountry == 'South Tajikistan' ? 'selected' : '' }}>South
                    Tajikistan</option>
                <option value="South Tanzania" {{ $selectedCountry == 'South Tanzania' ? 'selected' : '' }}>South Tanzania
                </option>

                {{-- 
                <option value="is" {{ $selectedCountry == 'is' ? 'selected' : '' }}>islamic</option>
                <option value="ch" {{ $selectedCountry == 'ch' ? 'selected' : '' }}>christian</option> --}}

            </select>

            <label for="lang" style="margin-right: 10px;">Language:</label>
            <select name="lang" id="lang" onchange="this.form.submit()"
                style="padding: 5px; border-radius: 4px; border: 1px solid #dddddd80;">
                <option value="ar" {{ request('lang', 'ar') == 'ar' ? 'selected' : '' }}>Arabic</option>
                <option value="en" {{ request('lang') == 'en' ? 'selected' : '' }}>English</option>
            </select>
        </form>
    </div>

    <div id="calendar" class="calendar-container"></div>
@endsection

@section('scripts')
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <!-- Add English locale -->
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/locales-all.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const events = {!! json_encode($events) ?? '[]' !!};
            const weekendDays = {!! json_encode($weekendDays) ?? '[0, 6]' !!};

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                events: events,
                locale: '{{ request('lang', 'ar') }}', // Dynamic locale based on user selection
                firstDay: 1,
                weekends: true,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: '{{ request('lang', 'ar') === 'en' ? 'Today' : 'اليوم' }}',
                    month: '{{ request('lang', 'ar') === 'en' ? 'Month' : 'شهر' }}',
                    week: '{{ request('lang', 'ar') === 'en' ? 'Week' : 'أسبوع' }}',
                    day: '{{ request('lang', 'ar') === 'en' ? 'Day' : 'يوم' }}'
                },
                datesSet: function(info) {
                    document.querySelectorAll('.fc-day').forEach(day => {
                        day.classList.remove('custom-weekend');
                        const dateAttr = day.getAttribute('data-date');
                        const dayOfWeek = dateAttr ? new Date(dateAttr).getDay() : parseInt(day
                            .getAttribute('data-day'));
                        if (weekendDays.includes(dayOfWeek)) {
                            day.classList.add('custom-weekend');
                        }
                    });
                }
            });

            calendar.render();
        });
    </script>
@endsection
