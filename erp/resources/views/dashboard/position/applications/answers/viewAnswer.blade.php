@extends('layouts.master')

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">  @lang('applications.application_answers')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}"> @lang('applications.dashboard')</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('positions.index') }}">@lang('applications.positions')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('applications.application_answers')</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title"> </div>
                            @lang('applications.answers_app') #{{ $application->id }}
                            <span class="badge bg-primary ms-2">
                                {{ $application->position->getNameAttribute() ?? 'Unknown Position' }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40%"> @lang('applications.question')</th>
                                        <th> @lang('applications.answer')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($answers as $answer)
                                        <tr>
                                            <td>{{ $answer['question'] }}</td>
                                            <td>{!! $answer['answer'] !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">

                            <div class="mt-3">

                                <a href="{{ route('position.applications.answers', $application->position_id) }}"
                                    class="btn btn-secondary">
                                      @lang('applications.returnapplications')
                                </a>
                                @if (auth('admin')->user()->hasPermissionTo('update applications answers', 'admin'))
                                    <div class="dropdown float-end d-inline-block ms-2">
                                        <button class="btn btn-primary dropdown-toggle" type="button" id="statusDropdown"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa fa-cog me-2"></i> Change Status @lang('applications.changestatus')
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="statusDropdown">
                                            @foreach (['new_request', 'accepted', 'pending_interview', 'rejected', 'incomplete_information', 'on_hold'] as $status)
                                                <li>
                                                    <form
                                                        action="{{ route('applications.updateStatus', $application->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="{{ $status }}">
                                                        <button type="submit"
                                                            class="dropdown-item
                                                        @if ($application->status === $status) active @endif">
                                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                        </button>
                                                    </form>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
