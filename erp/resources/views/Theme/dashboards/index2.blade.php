@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')

                    <!-- PAGE HEADER -->
                    <div class="page-header-breadcrumb d-md-flex d-block align-items-center justify-content-between ">
                        <h4 class="fw-medium mb-0">Crypto-Dashboard</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);" class="text-white-50">Dashboards</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Crypto</li>
                        </ol>
                    </div>
                    <!-- END PAGE HEADER -->

                    <!-- APP CONTENT -->
                    <div class="main-content app-content">
                        <div class="container-fluid">
                            <!-- Start::page-header -->

                            <!-- Start::row-1 -->

                            <div class="row">
                                <div class="col-xxl-3 col-lg-6">
                                    <div class="card custom-card overflow-hidden">
                                        <div class="card-body">
                                            <div class="d-flex align-items-top justify-content-between">
                                                <div>
                                                    <span class="avatar avatar-md avatar-rounded bg-light p-2">
                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                    </span>
                                                </div>
                                                <div class="flex-fill ms-3">
                                                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                        <div>
                                                            <p class="text-muted mb-0">Bitcoin
                                                                <span class="ms-1 badge bg-success-transparent badge-sm rounded-pill">+25.8%</span></p>
                                                            <h3 class="fw-semibold mb-0">
                                                                $12,800
                                                            </h3>
                                                        </div>
                                                        <div>
                                                            <span id="crm-total-customers"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-lg-6">
                                    <div class="card custom-card overflow-hidden">
                                        <div class="card-body">
                                            <div class="d-flex align-items-top justify-content-between">
                                                <div>
                                                    <span class="avatar avatar-md avatar-rounded me-2 p-2 bg-light">
                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Ethereum.svg')}}" alt="">
                                                    </span>
                                                </div>
                                                <div class="flex-fill ms-3">

                                                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                        <div>
                                                            <p class="text-muted mb-0">Ethereum
                                                                <span class="ms-1 badge bg-success-transparent badge-sm rounded-pill">+35.9%</span></p>
                                                            <h3 class="fw-semibold mb-0">$18,600
                                                            </h3>
                                                        </div>
                                                        <div id="crm-total-revenue"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-lg-6">
                                    <div class="card custom-card overflow-hidden">
                                        <div class="card-body">
                                            <div class="d-flex align-items-top justify-content-between">
                                                <div>
                                                    <span class="avatar avatar-md avatar-rounded bg-light p-2">
                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Dash.svg')}}" alt="">
                                                    </span>
                                                </div>
                                                <div class="flex-fill ms-3">
                                                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                        <div>
                                                            <p class="text-muted mb-0">Dash
                                                                <span class="ms-1 badge bg-success-transparent badge-sm rounded-pill">+15.9%</span></p>
                                                            <h3 class="fw-semibold mb-0">$16,580
                                                            </h3>
                                                        </div>
                                                        <div id="crm-conversion-ratio"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-lg-6">
                                    <div class="card custom-card overflow-hidden">
                                        <div class="card-body">
                                            <div class="d-flex align-items-top justify-content-between">
                                                <div>
                                                    <span class="avatar avatar-md avatar-rounded me-2 p-2 bg-light">
                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Euro.svg')}}" alt="">
                                                    </span>
                                                </div>
                                                <div class="flex-fill ms-3">
                                                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                        <div>
                                                            <p class="text-muted mb-0">Euro
                                                                <span class="ms-1 badge bg-success-transparent badge-sm rounded-pill">+35.7%</span></p>
                                                            <h3 class="fw-semibold mb-0">$76,560
                                                            </h3>
                                                        </div>
                                                        <div id="crm-total-deals"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xl-4">
                                    <div class="row row-deck">
                                        <div class="col-xl-12">
                                            <div class="card custom-card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <p class="mb-0 text-muted">Your Balance</p>
                                                            <h5 class="text-xl fw-semibold mb-0">$23,895.00</h5>
                                                            <p class=" text-muted fs-12 mb-3">1.5002746BTC</p>
                                                        </div>
                                                        <div class="dropdown">
                                                            <a href="javascript:void(0);" class="btn-outline-light btn btn-sm text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                                View All<i class="ri-arrow-down-s-line align-middle ms-1"></i>
                                                            </a>
                                                            <ul class="dropdown-menu" role="menu">
                                                                <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">Today</a></li>
                                                                <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">This Week</a></li>
                                                                <li><a class="dropdown-item" href="javascript:void(0);">Last Week</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-xl-6 col-sm-6 col-6">
                                                            <div class="d-sm-flex align-items-center flex-wrap">
                                                                <div class="me-sm-2 mb-2 mb-sm-0">
                                                                    <div class="avatar bg-primary-transparent tx-primary br-5">
                                                                        <i class="ti ti-arrow-big-up-lines fs-20"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-1">
                                                                    <span class="text-md mb-1 fw-semibold">$22,490.00</span>
                                                                    <p class="mb-0 fs-12  text-muted">Invested</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-6 col-sm-6 col-6">
                                                            <div class="d-sm-flex align-items-center flex-wrap">
                                                                <div class="me-sm-2 mb-2 mb-sm-0">
                                                                    <div class="avatar bg-primary-transparent tx-primary br-5">
                                                                        <i class="ti ti-corner-up-right-double fs-20"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-1">
                                                                    <span class="text-md mb-1  fw-semibold">$22,490.00</span>
                                                                    <p class="mb-0 fs-12  text-muted">Expenses</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex">
                                                        <a href="javascript:void(0);" class="btn btn-secondary flex-1 me-2"><i class="ti ti-arrow-bar-to-down fs-16 me-2 d-inline-flex"></i>Withdraw Money</a>
                                                        <a href="javascript:void(0);" class="btn btn-primary flex-1"><i class="ti ti-send fs-16 me-2 d-inline-flex"></i>Deposit Money</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="card custom-card">

                                                <div class="card-header justify-content-between">
                                                    <div class="card-title">Prediction Meter</div>
                                                    <select class="form-control" data-trigger>
                                                        <option value="">BTC</option>
                                                        <option value="Choice 1">ETH</option>
                                                        <option value="Choice 2">XRP</option>
                                                        <option value="Choice 3">DASH</option>
                                                        <option value="Choice 4">NEO</option>
                                                        <option value="Choice 5">LTC</option>
                                                        <option value="Choice 6">BSD</option>
                                                    </select>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col-xxl-6">
                                                            <div id="trademeter"></div>
                                                        </div>
                                                        <div class="col-xxl-6">
                                                            <div class="d-flex align-items-center mb-3">
                                                                <div class="flex-1 d-flex align-items-center">
                                                                    <div class="me-2">
                                                                        <span class="avatar avatar-xs rounded-circle bg-success-transparent"><i class="ti ti-bolt fs-14"></i></span>
                                                                    </div>
                                                                    <span>Sell</span>
                                                                </div>
                                                                <div class="text-end">
                                                                    <span class="badge bg-success-transparent badge-sm">0 - 45</span>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex align-items-center mb-3">
                                                                <div class="flex-1 d-flex align-items-center">
                                                                    <div class="me-2">
                                                                        <span class="avatar avatar-xs rounded-circle bg-primary-transparent"><i class="ti ti-activity-heartbeat fs-14"></i></span>
                                                                    </div>
                                                                    <span>Neutral</span>
                                                                </div>
                                                                <div class="text-end">
                                                                    <span class="badge bg-primary-transparent badge-sm">45 - 65</span>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-1 d-flex align-items-center">
                                                                    <div class="me-2">
                                                                        <span class="avatar avatar-xs rounded-circle bg-secondary-transparent"><i class="ti ti-bolt fs-14"></i></span>
                                                                    </div>
                                                                    <span>Buy</span>
                                                                </div>
                                                                <div class="text-end">
                                                                    <span class="badge bg-secondary-transparent badge-sm">65 Above</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="card custom-card">
                                                <div class="card-header justify-content-between">
                                                    <div class="card-title">Converter</div>
                                                    <div class="dropdown">
                                                        <a href="javascript:void(0);" class="btn btn-sm btn-primary-light" data-bs-toggle="dropdown">Euro (€)<i class="ti ti-chevron-down v-middle fs-16 ms-1"></i></a>
                                                        <div class="dropdown-menu fs-13">
                                                            <a class="dropdown-item " href="javascript:void(0);">USD</a>
                                                            <a class="dropdown-item active" href="javascript:void(0);">Euro (€)</a>
                                                            <a class="dropdown-item" href="javascript:void(0);">FJD ($)</a>
                                                            <a class="dropdown-item" href="javascript:void(0);">INR (₹)</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="d-sm-flex px-4 pt-3 border-top-dashed border-bottom-dashed">
                                                        <p class=" text-muted mb-0 d-inline-flex">Bal Available:<span class="text-primary text-sm ms-1">$25,390.00</span></p>
                                                        <p class=" text-muted mb-0 ms-sm-auto text-end">1 USD:<span class="text-primary text-sm ms-1">	~ 0.000158BTC</span></p>
                                                    </div>
                                                    <div class="p-4">
                                                        <div class="form-group mb-3">
                                                            <div class="input-group">
                                                                <span class="input-group-text">Pay</span>
                                                                <input class="form-control" type="number" value="0.0329" placeholder="Emount of coins">
                                                                <span class="input-group-text p-0 crypto-buy-sell   border-0">
                                                                    <select class="form-control" data-trigger>
                                                                        <option value="">BTC</option>
                                                                        <option value="Choice 1">ETH</option>
                                                                        <option value="Choice 2">XRP</option>
                                                                        <option value="Choice 3">DASH</option>
                                                                        <option value="Choice 4">NEO</option>
                                                                        <option value="Choice 5">LTC</option>
                                                                        <option value="Choice 6">BSD</option>
                                                                    </select>
                                                                </span>
                                                            </div>
                                                            <a aria-label="anchor" href="javascript:void(0);" class="d-block btn btn-secondary-light btn-icon btn-sm mx-auto my-3"><i class="ti ti-arrows-down-up"></i></a>
                                                            <div class="input-group">
                                                                <span class="input-group-text">Buy</span>
                                                                <input class="form-control" type="number" value="0.00378" placeholder="Emount you get">
                                                                <span class="input-group-text p-0  crypto-buy-sell  border-0">
                                                                    <select class="form-control" data-trigger>
                                                                        <option value="">BTC</option>
                                                                        <option value="Choice 1">ETH</option>
                                                                        <option value="Choice 2">XRP</option>
                                                                        <option value="Choice 3">DASH</option>
                                                                        <option value="Choice 4">NEO</option>
                                                                        <option value="Choice 5">LTC</option>
                                                                        <option value="Choice 6">BSD</option>
                                                                    </select>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="p-3 rounded-1 bd-gray-100 mb-3">
                                                            <div class="row">
                                                                <div class="col-xl-6 mb-2">
                                                                    <span>Transaction Fee</span>
                                                                </div>
                                                                <div class="col-xl-6 mb-2">
                                                                    <span class="float-end">$2.05</span>
                                                                </div>
                                                                <div class="col-xl-6 mb-2">
                                                                    <span>Other Charges</span>
                                                                </div>
                                                                <div class="col-xl-6 mb-2">
                                                                    <span class="float-end">$7.73</span>
                                                                </div>
                                                                <div class="col-xl-6">
                                                                    <span class="fw-semibold text-md">Total</span>
                                                                </div>
                                                                <div class="col-xl-6">
                                                                    <span class="float-end fw-semibold text-md">$34,798.00</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="d-grid">
                                                            <a href="javascript:void(0);" class="btn btn-primary btn-wave waves-effect d-inline-flex justify-content-center">Exchange
                                                                <i class="ti ti-arrow-narrow-right v-middle fs-16 ms-2"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-8">
                                    <div class="row row-deck">
                                        <div class="col-xl-12">
                                            <div class="card custom-card">
                                                <div class="card-header flex-between">
                                                    <h6 class="card-title">Statistics</h6>
                                                    <div class="btn-group">
                                                        <a href="javascript:void(0);" class="btn btn-sm btn-primary-light">1H</a>
                                                        <a href="javascript:void(0);" class="btn btn-sm btn-primary-light">6H</a>
                                                        <a href="javascript:void(0);" class="btn btn-sm btn-primary-light">12H</a>
                                                        <a href="javascript:void(0);" class="btn btn-sm btn-primary">1D</a>
                                                    </div>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="px-4 py-3 flex-wrap border-top-dashed border-bottom-dashed d-flex justify-content-between">
                                                        <div class="me-1 min-w-fit-content">
                                                            <span class="text-lg fw-semibold ">$18,589.00</span>
                                                            <span class="ms-2 badge bg-success rounded-pill">+12.6%</span>
                                                            <div class="input-group mt-1 crypto-buy-sell">
                                                                <input type="text" class="form-control crypto-buy-sell-input flex-none" aria-label="crypto buy select" placeholder="Select Currency">
                                                                <select class="form-control" data-trigger>
                                                                    <option value="">BTC</option>
                                                                    <option value="Choice 1">ETH</option>
                                                                    <option value="Choice 2">XRP</option>
                                                                    <option value="Choice 3">DASH</option>
                                                                    <option value="Choice 4">NEO</option>
                                                                    <option value="Choice 5">LTC</option>
                                                                    <option value="Choice 6">BSD</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-wrap flex-1 justify-content-end my-auto  mt-2 mt-sm-0">
                                                            <div class="me-4">
                                                                <span class=" text-muted fs-12">Last Price</span>
                                                                <p class="mb-0 text-sm  fw-semibold">$12,577.58</p>
                                                            </div>
                                                            <div class="me-4">
                                                                <span class=" text-muted fs-12">24h Volume</span>
                                                                <p class="mb-0 text-sm  fw-semibold">85.45BTC</p>
                                                            </div>
                                                            <div class="me-4">
                                                                <span class=" text-muted fs-12">Open</span>
                                                                <p class="mb-0 text-sm  fw-semibold">42,397.89</p>
                                                            </div>
                                                            <div class="me-4">
                                                                <span class=" text-muted fs-12">High</span>
                                                                <p class="mb-0 text-sm  fw-semibold">99,373.38</p>
                                                            </div>
                                                            <div class="me-0">
                                                                <span class=" text-muted fs-12">Close</span>
                                                                <p class="mb-0 text-sm  fw-semibold">85,158.74</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="marketCap" class="p-4 pt-0"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-5">
                                            <div class="card custom-card">
                                                <div class="card-header justify-content-between">
                                                    <div class="card-title">Recent Transactions</div>
                                                    <a href="javascript:void(0);" class="btn btn-primary-light btn-sm"><i class="ti ti-history fs-14 me-2 d-inline-block"></i>Full History</a>
                                                </div>
                                                <div class="card-body">
                                                    <ul class="list-unstyled mb-0">
                                                        <li class="mb-3">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2">
                                                                    <span class="avatar avatar-md rounded-circle bg-pink-transparent tx-pink"><i class="ti ti-coin fs-16"></i></span>
                                                                </div>
                                                                <div class="flex-1">
                                                                    <div class="d-flex align-items-start justify-content-between">
                                                                        <span class="fw-semibold">Bought Bitcoin</span>
                                                                        <span class="text-pink text-end">-$1,250</span>
                                                                    </div>
                                                                    <div class="d-flex align-items-start justify-content-between fs-13">
                                                                        <span class="text-muted">Success</span>
                                                                        <span class=" text-muted text-end">+0.0012BTC</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="mb-3">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2">
                                                                    <span class="avatar avatar-md rounded-circle bg-secondary-transparent tx-secondary"><i class="ti ti-coin fs-16"></i></span>
                                                                </div>
                                                                <div class="flex-1">
                                                                    <div class="d-flex align-items-start justify-content-between">
                                                                        <span class="fw-semibold">Bought Bitcoin</span>
                                                                        <span class="text-danger text-end">-$1,250</span>
                                                                    </div>
                                                                    <div class="d-flex align-items-start justify-content-between fs-13">
                                                                        <span class="text-muted">Failed</span>
                                                                        <span class=" text-muted text-end">+0.0012BTC</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="mb-3">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2">
                                                                    <span class="avatar avatar-md rounded-circle bg-warning-transparent"><i class="ti ti-send fs-16"></i></span>
                                                                </div>
                                                                <div class="flex-1">
                                                                    <div class="d-flex align-items-start justify-content-between">
                                                                        <span class="fw-semibold">Sell Bitcoin</span>
                                                                        <span class="text-warning text-end">+$12,205</span>
                                                                    </div>
                                                                    <div class="d-flex align-items-start justify-content-between fs-13">
                                                                        <span class="text-muted">Success</span>
                                                                        <span class=" text-muted text-end">-0.0015BTC</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="mb-3">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2">
                                                                    <span class="avatar avatar-md rounded-circle bg-success-transparent"><i class="ti ti-send fs-16"></i></span>
                                                                </div>
                                                                <div class="flex-1">
                                                                    <div class="d-flex align-items-start justify-content-between">
                                                                        <span class="fw-semibold">Sell Bitcoin</span>
                                                                        <span class="text-success text-end">+$12,205</span>
                                                                    </div>
                                                                    <div class="d-flex align-items-start justify-content-between fs-13">
                                                                        <span class="text-muted">Failed</span>
                                                                        <span class=" text-muted text-end">-0.0015BTC</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="mb-3">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2">
                                                                    <span class="avatar avatar-md rounded-circle bg-info-transparent tx-info"><i class="ti ti-user fs-16"></i></span>
                                                                </div>
                                                                <div class="flex-1">
                                                                    <div class="d-flex align-items-start justify-content-between">
                                                                        <span class="fw-semibold">Sent to Afgan Javebi</span>
                                                                        <span class="text-info text-end">-$5,152</span>
                                                                    </div>
                                                                    <div class="d-flex align-items-start justify-content-between fs-13">
                                                                        <span class="text-muted">Pending</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2">
                                                                    <span class="avatar avatar-md rounded-circle bg-purple-transparent tx-purple"><i class="ti ti-user fs-16"></i></span>
                                                                </div>
                                                                <div class="flex-1">
                                                                    <div class="d-flex align-items-start justify-content-between">
                                                                        <span class="fw-semibold">Sent to Arif Maura</span>
                                                                        <span class="text-purple text-end">-$4,350</span>
                                                                    </div>
                                                                    <div class="d-flex align-items-start justify-content-between fs-13">
                                                                        <span class="text-muted">Success</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-7">
                                            <div class="card custom-card overflow-hidden">
                                                <div class="card-header justify-content-between">
                                                    <div class="card-title">
                                                        Your Assets
                                                    </div>
                                                    <div class="dropdown">
                                                        <a href="javascript:void(0);" class="btn-outline-light btn btn-sm text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                            View All<i class="ri-arrow-down-s-line align-middle ms-1"></i>
                                                        </a>
                                                        <ul class="dropdown-menu" role="menu">
                                                            <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">Today</a></li>
                                                            <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">This Week</a></li>
                                                            <li><a class="dropdown-item" href="javascript:void(0);">Last Week</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table card-table table-vcenter text-nowrap mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col">Currency</th>
                                                                    <th scope="col">Total</th>
                                                                    <th scope="col">Holding</th>
                                                                    <th scope="col">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="active-tab">
                                                                <tr>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="lh-1">
                                                                                <span class="avatar avatar-md avatar-rounded me-2 p-2 bg-light">
                                                                                    <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                                                </span>
                                                                            </div>
                                                                            <div class="align-items-center">
                                                                                <span class="fs-12">Bitcoin</span>
                                                                                <p class="mb-0 text-muted fs-12">(0.0092312)</p>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span>$1,11,290</span>
                                                                    </td>
                                                                    <td>
                                                                        <span>1.00089BTC</span>
                                                                    </td>
                                                                    <td class="">
                                                                        <a href="javascript:void(0);" class="btn btn-secondary btn-sm me-1">Buy</a>
                                                                        <a href="javascript:void(0);" class="btn btn-primary btn-sm">Sell</a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="lh-1">
                                                                                <span class="avatar avatar-md avatar-rounded me-2 p-2 bg-light">
                                                                                    <img src="{{asset('build/assets/images/crypto-currencies/regular/Ethereum.svg')}}" alt="">
                                                                                </span>
                                                                            </div>
                                                                            <div class="align-items-center">
                                                                                <span class="fs-12">Etherium </span>
                                                                                <p class="mb-0 text-muted fs-12">(0.9207702)</p>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span>$43,11,290</span>
                                                                    </td>
                                                                    <td>
                                                                        <span>1.00089ETH</span>
                                                                    </td>
                                                                    <td class="">
                                                                        <a href="javascript:void(0);" class="btn btn-secondary btn-sm me-1">Buy</a>
                                                                        <a href="javascript:void(0);" class="btn btn-primary btn-sm">Sell</a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="lh-1">
                                                                                <span class="avatar avatar-md avatar-rounded me-2 p-2 bg-light">
                                                                                    <img src="{{asset('build/assets/images/crypto-currencies/regular/Dash.svg')}}" alt="">
                                                                                </span>
                                                                            </div>
                                                                            <div class="align-items-center">
                                                                                <span class="fs-12">Dash</span>
                                                                                <p class="mb-0 text-muted fs-12">(0.83092333)</p>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span>$6,11,290</span>
                                                                    </td>
                                                                    <td>
                                                                        <span>1.00089DAH</span>
                                                                    </td>
                                                                    <td class="">
                                                                        <a href="javascript:void(0);" class="btn btn-secondary btn-sm me-1">Buy</a>
                                                                        <a href="javascript:void(0);" class="btn btn-primary btn-sm">Sell</a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="lh-1">
                                                                                <span class="avatar avatar-md avatar-rounded me-2 p-2 bg-light">
                                                                                    <img src="{{asset('build/assets/images/crypto-currencies/regular/Euro.svg')}}" alt="">
                                                                                </span>
                                                                            </div>
                                                                            <div class="align-items-center">
                                                                                <span class="fs-12">Euro</span>
                                                                                <p class="mb-0 text-muted fs-12">(0.0092363)<span class="text-muted"></span></p>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span>$93,11,290</span>
                                                                    </td>
                                                                    <td>
                                                                        <span>1.00089EUR</span>
                                                                    </td>
                                                                    <td class="">
                                                                        <a href="javascript:void(0);" class="btn btn-secondary btn-sm me-1">Buy</a>
                                                                        <a href="javascript:void(0);" class="btn btn-primary btn-sm">Sell</a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="lh-1">
                                                                                <span class="avatar avatar-md avatar-rounded me-2 p-2 bg-light">
                                                                                    <img src="{{asset('build/assets/images/crypto-currencies/regular/litecoin.svg')}}" alt="">
                                                                                </span>
                                                                            </div>
                                                                            <div class="align-items-center">
                                                                                <span class="fs-12 text-muted">Litecoin</span>
                                                                                <p class="mb-0 text-muted fs-12">(1.00009023)</p>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span>$7,11,290</span>
                                                                    </td>
                                                                    <td>
                                                                        <span>1.00089LIT</span>
                                                                    </td>
                                                                    <td class="">
                                                                        <a href="javascript:void(0);" class="btn btn-secondary btn-sm me-1">Buy</a>
                                                                        <a href="javascript:void(0);" class="btn btn-primary btn-sm">Sell</a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--End::row-1 -->

                            <!-- Start:: row-3 -->
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="card custom-card">
                                        <div class="card-header justify-content-between flex-wrap">
                                            <div class="card-title">
                                                Crypto currencies Market Value
                                            </div>
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                <button type="button" class="btn btn-primary btn-sm btn-wave">1D</button>
                                                <button type="button" class="btn btn-primary-light btn-sm btn-wave">1W</button>
                                                <button type="button" class="btn btn-primary-light btn-sm btn-wave">1M</button>
                                                <button type="button" class="btn btn-primary-light btn-sm btn-wave">3M</button>
                                                <button type="button" class="btn btn-primary-light btn-sm btn-wave">6M</button>
                                                <button type="button" class="btn btn-primary-light btn-sm btn-wave">1Y</button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover text-nowrap table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">S.No</th>
                                                            <th scope="col">Name</th>
                                                            <th scope="col">Price</th>
                                                            <th scope="col">24H Volume</th>
                                                            <th scope="col">MarketCap</th>
                                                            <th scope="col">Price Graph</th>
                                                            <th scope="col">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>1</td>
                                                            <td>
                                                                <div class="lh-1 d-flex align-items-center">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                                    </span> - BTC
                                                                </div>
                                                            </td>
                                                            <td>
                                                                $16,839.10
                                                            </td>
                                                            <td class="">
                                                                <span class="badge badge-sm bg-success-transparent rounded-pill">+12.02%</span>
                                                            </td>
                                                            <td>
                                                                14,674,311,168
                                                            </td>
                                                            <td>
                                                            <div id="bitcoin-price-graph"></div>
                                                            </td>
                                                            <td class="">
                                                                <div class="btn-list">
                                                                    <a href="javascript:void(0);" class="btn btn-primary-light btn-sm me-1">Buy</a>
                                                                    <a href="javascript:void(0);" class="btn btn-secondary-light btn-sm">Trade</a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>2</td>
                                                            <td>
                                                                <div class="lh-1 d-flex align-items-center">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Ethereum.svg')}}" alt="">
                                                                    </span> - ETH
                                                                </div>
                                                            </td>
                                                            <td>
                                                                1,217.96
                                                            </td>
                                                            <td class="">
                                                                <span class="badge badge-sm bg-success-transparent rounded-pill">+142.05%</span>
                                                            </td>
                                                            <td>
                                                                $4,758,554,801
                                                            </td>
                                                            <td>
                                                                <div id="etherium-price-graph"></div>
                                                            </td>
                                                            <td class="">
                                                                <div class="btn-list">
                                                                    <a href="javascript:void(0);" class="btn btn-primary-light btn-sm me-1">Buy</a>
                                                                    <a href="javascript:void(0);" class="btn btn-secondary-light btn-sm">Trade</a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>3</td>
                                                            <td>
                                                                <div class="lh-1 d-flex align-items-center">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Dash.svg')}}" alt="">
                                                                    </span> - DASH
                                                                </div>
                                                            </td>
                                                            <td>
                                                                $43.49
                                                            </td>
                                                            <td class="">
                                                                <span class="badge badge-sm bg-success-transparent rounded-pill">+712.02%</span>
                                                            </td>
                                                            <td class="">
                                                                54,758,554,801
                                                            </td>
                                                            <td>
                                                                <div id="dash-price-graph"></div>
                                                            </td>
                                                            <td class="">
                                                                <div class="btn-list">
                                                                    <a href="javascript:void(0);" class="btn btn-primary-light btn-sm me-1">Buy</a>
                                                                    <a href="javascript:void(0);" class="btn btn-secondary-light btn-sm">Trade</a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>4</td>
                                                            <td>
                                                                <div class="lh-1 d-flex align-items-center">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Ripple.svg')}}" alt="">
                                                                    </span> - XRP
                                                                </div>
                                                            </td>
                                                            <td>
                                                                $0.3531
                                                            </td>
                                                            <td class="">
                                                                <span class="badge badge-sm bg-danger-transparent rounded-pill">+12.02%</span>
                                                            </td>
                                                            <td>
                                                                $511,598,941
                                                            </td>
                                                            <td>
                                                                <div id="ripple-price-graph"></div>
                                                            </td>
                                                            <td class="">
                                                                <div class="btn-list">
                                                                    <a href="javascript:void(0);" class="btn btn-primary-light btn-sm me-1">Buy</a>
                                                                    <a href="javascript:void(0);" class="btn btn-secondary-light btn-sm">Trade</a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>5</td>
                                                            <td>
                                                                <div class="lh-1 d-flex align-items-center">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/IOTA.svg')}}" alt="">
                                                                    </span> - IOTA
                                                                </div>
                                                            </td>
                                                            <td>
                                                                $0.169741
                                                            </td>
                                                            <td class="">
                                                                <span class="badge badge-sm bg-success-transparent rounded-pill">+542.02%</span>
                                                            </td>
                                                            <td>
                                                                $5,524,385
                                                            </td>
                                                            <td>
                                                                <div id="iota-price-graph"></div>
                                                            </td>
                                                            <td class="">
                                                                <div class="btn-list">
                                                                    <a href="javascript:void(0);" class="btn btn-primary-light btn-sm me-1">Buy</a>
                                                                    <a href="javascript:void(0);" class="btn btn-secondary-light btn-sm">Trade</a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>6</td>
                                                            <td>
                                                                <div class="lh-1 d-flex align-items-center">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Neo.svg')}}" alt="">
                                                                    </span> - NEO
                                                                </div>
                                                            </td>
                                                            <td>
                                                                $6.43
                                                            </td>
                                                            <td class="">
                                                                <span class="badge badge-sm bg-danger-transparent rounded-pill">+12.02%</span>
                                                            </td>
                                                            <td>
                                                                $12,904,320
                                                            </td>
                                                            <td>
                                                                <div id="neo-price-graph"></div>
                                                            </td>
                                                            <td class="">
                                                                <div class="btn-list">
                                                                    <a href="javascript:void(0);" class="btn btn-primary-light btn-sm me-1">Buy</a>
                                                                    <a href="javascript:void(0);" class="btn btn-secondary-light btn-sm">Trade</a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    Showing 6 Entries <i class="bi bi-arrow-right ms-2 fw-semibold"></i>
                                                </div>
                                                <div class="ms-auto">
                                                    <nav aria-label="Page navigation" class="pagination-style-4">
                                                        <ul class="pagination mb-0">
                                                            <li class="page-item disabled">
                                                                <a class="page-link" href="javascript:void(0);">
                                                                    Prev
                                                                </a>
                                                            </li>
                                                            <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                                                            <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                                            <li class="page-item">
                                                                <a class="page-link text-primary" href="javascript:void(0);">
                                                                    next
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </nav>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-3 -->

                        </div>
                    </div>
                    <!-- END APP CONTENT -->

@endsection

@section('scripts')

        <!-- APEX CHARTS JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!-- CRYPTO-DASHBOARD JS -->
        @vite('resources/assets/js/crypto-dashboard.js')
        

@endsection
    
    

    