@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')

                    <!-- PAGE HEADER -->
                    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
                        <h4 class="fw-medium mb-0">Form Layouts</h4>
                        <div class="ms-sm-1 ms-0">
                            <nav>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Forms</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Form Layouts</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!-- PAGE HEADER -->

                    <!-- APP-CONTENT START -->
                    <div class="main-content app-content">
                        <div class="container-fluid">

                            <!-- Start:: row-1 -->
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Vertical Forms
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="form-text" class="form-label fs-14 text-dark">Enter name</label>
                                                <input type="text" class="form-control" id="form-text" placeholder="">
                                            </div>
                                            <div class="mb-3">
                                                <label for="form-password" class="form-label fs-14 text-dark">Enter
                                                    Password</label>
                                                <input type="password" class="form-control" id="form-password" placeholder="">
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" value="" id="invalidCheck"
                                                    required="">
                                                <label class="form-check-label" for="invalidCheck">
                                                    Accept Policy
                                                </label>
                                            </div>
                                            <button class="btn btn-primary" type="submit">Submit</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Horizontal form
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <form>
                                                <div class="row mb-3">
                                                    <label for="inputEmail3"
                                                        class="col-sm-2 col-form-label">Email</label>
                                                    <div class="col-sm-10">
                                                        <input type="email" class="form-control" id="inputEmail3">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <label for="inputPassword3"
                                                        class="col-sm-2 col-form-label">Password</label>
                                                    <div class="col-sm-10">
                                                        <input type="password" class="form-control" id="inputPassword3">
                                                    </div>
                                                </div>
                                                <fieldset class="row mb-3">
                                                    <legend class="col-form-label col-sm-2 pt-0">Qualified</legend>
                                                    <div class="col-sm-10">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                name="gridRadios" id="gridRadios1" value="option1"
                                                                checked>
                                                            <label class="form-check-label" for="gridRadios1">
                                                                Prelims
                                                            </label>
                                                        </div>
                                                        <div class="form-check disabled">
                                                            <input class="form-check-input" type="radio"
                                                                name="gridRadios" id="gridRadios2" value="option3"
                                                                disabled>
                                                            <label class="form-check-label" for="gridRadios2">
                                                                Mains
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="gridCheck1">
                                                            <label class="form-check-label" for="gridCheck1">
                                                                Certified
                                                            </label>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                                <button type="submit" class="btn btn-primary">Sign in</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-1 -->

                            <!-- Start:: row-2 -->
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Inline forms
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <form class="row row-cols-lg-auto g-3 align-items-center">
                                                <div class="col-12">
                                                    <label class="visually-hidden"
                                                        for="inlineFormInputGroupUsername">Username</label>
                                                    <div class="input-group">
                                                        <div class="input-group-text">@</div>
                                                        <input type="text" class="form-control"
                                                            id="inlineFormInputGroupUsername" placeholder="Username">
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <label class="visually-hidden"
                                                        for="inlineFormSelectPref">Preference</label>
                                                    <select class="form-select" id="inlineFormSelectPref">
                                                        <option selected>Choose...</option>
                                                        <option value="1">One</option>
                                                        <option value="2">Two</option>
                                                        <option value="3">Three</option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="inlineFormCheck">
                                                        <label class="form-check-label" for="inlineFormCheck">
                                                            Remember me
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Column sizing
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-sm-7">
                                                    <input type="text" class="form-control" placeholder="City"
                                                        aria-label="City">
                                                </div>
                                                <div class="col-sm">
                                                    <input type="text" class="form-control" placeholder="State"
                                                        aria-label="State">
                                                </div>
                                                <div class="col-sm">
                                                    <input type="text" class="form-control" placeholder="Zip"
                                                        aria-label="Zip">
                                                </div>
                                            </div> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-2 -->

                            <!-- Start:: row-3 -->
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Utilities
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="formGroupExampleInput" class="form-label">Example label</label>
                                                <input type="text" class="form-control" id="formGroupExampleInput"
                                                    placeholder="Example input placeholder">
                                            </div>
                                            <div class="mb-1">
                                                <label for="formGroupExampleInput2" class="form-label">Another label</label>
                                                <input type="text" class="form-control" id="formGroupExampleInput2"
                                                    placeholder="Another input placeholder">
                                            </div>   
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Horizontal form label sizing
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <label for="colFormLabelSm"
                                                    class="col-sm-2 col-form-label col-form-label-sm">Email</label>
                                                <div class="col-sm-10">
                                                    <input type="email" class="form-control form-control-sm"
                                                        id="colFormLabelSm" placeholder="col-form-label-sm">
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="colFormLabel" class="col-sm-2 col-form-label">Email</label>
                                                <div class="col-sm-10">
                                                    <input type="email" class="form-control" id="colFormLabel"
                                                        placeholder="col-form-label">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label for="colFormLabelLg"
                                                    class="col-sm-2 col-form-label col-form-label-lg">Email</label>
                                                <div class="col-sm-10">
                                                    <input type="email" class="form-control form-control-lg"
                                                        id="colFormLabelLg" placeholder="col-form-label-lg">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>  
                            </div>
                            <!-- End:: row-3 -->

                            <!-- Start:: row-4 -->
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Vertical Forms with icon
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="form-text1" class="form-label fs-14 text-dark">Enter name</label>
                                                <div class="input-group">
                                                    <div class="input-group-text"><i class="ri-user-line"></i></div>
                                                    <input type="text" class="form-control" id="form-text1" placeholder="">
                                                </div>    
                                            </div>
                                            <div class="mb-3">
                                                <label for="form-password1" class="form-label fs-14 text-dark">Enter
                                                    Password</label>
                                                <div class="input-group">
                                                    <div class="input-group-text"><i class="ri-lock-line"></i></div>
                                                    <input type="password" class="form-control" id="form-password1" placeholder="">
                                                </div> 
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" value="" id="invalidCheck1"
                                                    required="">
                                                <label class="form-check-label" for="invalidCheck1">
                                                    Accept Policy
                                                </label>
                                            </div>
                                            <button class="btn btn-primary" type="submit">Submit</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Horizontal form With Icons
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <form>
                                                <div class="row mb-3">
                                                    <label for="inputEmail1"
                                                        class="col-sm-2 col-form-label">Email</label>
                                                    <div class="col-sm-10">
                                                        <div class="input-group">
                                                            <input type="email" class="form-control" id="inputEmail1">
                                                            <div class="input-group-text">
                                                                <i class="ri-mail-line"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <label for="inputPassword1"
                                                        class="col-sm-2 col-form-label">Password</label>
                                                    <div class="col-sm-10">
                                                        <div class="input-group">
                                                            <input type="password" class="form-control" id="inputPassword1">
                                                            <div class="input-group-text">
                                                                <i class="ri-lock-line"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <fieldset class="row mb-3">
                                                    <legend class="col-form-label col-sm-2 pt-0">Qualified</legend>
                                                    <div class="col-sm-10">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                name="gridRadios" id="gridRadios4" value="option1"
                                                                checked>
                                                            <label class="form-check-label" for="gridRadios4">
                                                                Prelims
                                                            </label>
                                                        </div>
                                                        <div class="form-check disabled">
                                                            <input class="form-check-input" type="radio"
                                                                name="gridRadios" id="gridRadios3" value="option3"
                                                                disabled>
                                                            <label class="form-check-label" for="gridRadios3">
                                                                Mains
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="gridCheck2">
                                                            <label class="form-check-label" for="gridCheck2">
                                                                Certified
                                                            </label>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                                <button type="submit" class="btn btn-primary">Sign in</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-4 -->

                            <!-- Start:: row-5 -->
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Auto sizing
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <form class="row gy-2 gx-3 align-items-center mb-4">
                                                <div class="col-auto">
                                                    <label class="visually-hidden" for="autoSizingInput">Name</label>
                                                    <input type="text" class="form-control" id="autoSizingInput"
                                                        placeholder="Jane Doe">
                                                </div>
                                                <div class="col-auto">
                                                    <label class="visually-hidden"
                                                        for="autoSizingInputGroup">Username</label>
                                                    <div class="input-group">
                                                        <div class="input-group-text">@</div>
                                                        <input type="text" class="form-control"
                                                            id="autoSizingInputGroup" placeholder="Username">
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <label class="visually-hidden"
                                                        for="autoSizingSelect">Preference</label>
                                                    <select class="form-select" id="autoSizingSelect">
                                                        <option selected>Choose...</option>
                                                        <option value="1">One</option>
                                                        <option value="2">Two</option>
                                                        <option value="3">Three</option>
                                                    </select>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="autoSizingCheck">
                                                        <label class="form-check-label" for="autoSizingCheck">
                                                            Remember me
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </form>
                                            <span class="fw-semibold mb-1 text-muted">You can then remix that once again with size-specific column
                                                classes.</span>
                                            <form class="row gx-3 gy-2 align-items-center mt-0">
                                                <div class="col-sm-3">
                                                    <label class="visually-hidden"
                                                        for="specificSizeInputName">Name</label>
                                                    <input type="text" class="form-control" id="specificSizeInputName"
                                                        placeholder="Jane Doe">
                                                </div>
                                                <div class="col-sm-3">
                                                    <label class="visually-hidden"
                                                        for="specificSizeInputGroupUsername">Username</label>
                                                    <div class="input-group">
                                                        <div class="input-group-text">@</div>
                                                        <input type="text" class="form-control"
                                                            id="specificSizeInputGroupUsername" placeholder="Username">
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <label class="visually-hidden"
                                                        for="specificSizeSelect">Preference</label>
                                                    <select class="form-select" id="specificSizeSelect">
                                                        <option selected>Choose...</option>
                                                        <option value="1">One</option>
                                                        <option value="2">Two</option>
                                                        <option value="3">Three</option>
                                                    </select>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="autoSizingCheck2">
                                                        <label class="form-check-label" for="autoSizingCheck2">
                                                            Remember me
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </form>  
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-5 -->

                            <!-- Start:: row-6 -->
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Form grid
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">First Name</label>
                                                    <input type="text" class="form-control" placeholder="First name"
                                                        aria-label="First name">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Last Name</label>
                                                    <input type="text" class="form-control" placeholder="Last name"
                                                        aria-label="Last name">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Address</label>
                                                    <div class="row">
                                                        <div class="col-xl-12 mb-3">
                                                            <input type="text" class="form-control" placeholder="Street"
                                                            aria-label="Street">
                                                        </div>
                                                        <div class="col-xl-12 mb-3">
                                                            <input type="text" class="form-control" placeholder="Landmark"
                                                            aria-label="Landmark">
                                                        </div>
                                                        <div class="col-xl-6 mb-3">
                                                            <input type="text" class="form-control" placeholder="City"
                                                            aria-label="City">
                                                        </div>
                                                        <div class="col-xl-6 mb-3">
                                                            <select id="inputState1" class="form-select">
                                                                <option selected>State/Province</option>
                                                                <option>...</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-xl-6 mb-3">
                                                            <input type="text" class="form-control" placeholder="Postal/Zip code"
                                                            aria-label="Postal/Zip code">
                                                        </div>
                                                        <div class="col-xl-6 mb-3">
                                                            <select id="inputCountry" class="form-select">
                                                                <option selected>Country</option>
                                                                <option>...</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="row">
                                                        <div class="col-xl-12 mb-3">
                                                            <label class="form-label">Email</label>
                                                            <input type="email" class="form-control" placeholder="Email"
                                                            aria-label="email">
                                                        </div>
                                                        <div class="col-xl-12 mb-3">
                                                            <label class="form-label">D.O.B</label>
                                                            <input type="date" class="form-control text-start"
                                                            aria-label="dateofbirth">
                                                        </div>
                                                        <div class="col-xl-12 mb-3">
                                                            <div class="row">
                                                                <label class="form-label mb-1">Maritial Status</label>
                                                                <div class="col-xl-6">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" value="" id="status-married" required="">
                                                                        <label class="form-check-label" for="status-married">
                                                                            Married
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-xl-6">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" value="" id="status-unmarried" required="">
                                                                        <label class="form-check-label" for="status-unmarried">
                                                                            Single
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-12">

                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Contact Number</label>
                                                    <input type="text" class="form-control" placeholder="Phone number"
                                                        aria-label="Phone number">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Alternative Contact</label>
                                                    <input type="text" class="form-control" placeholder="Phone number"
                                                        aria-label="Phone number">
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-check mb-3">
                                                        <input class="form-check-input" type="checkbox" id="gridCheck">
                                                        <label class="form-check-label" for="gridCheck">
                                                            Check me out
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <button type="submit" class="btn btn-primary">Sign in</button>
                                                </div>
                                            </div>   
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Gutters
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <form class="row g-3 mt-0">
                                                <div class="col-md-6">
                                                    <label class="form-label">First Name</label>
                                                    <input type="text" class="form-control" placeholder="First name"
                                                        aria-label="First name">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Last Name</label>
                                                    <input type="text" class="form-control" placeholder="Last name"
                                                        aria-label="Last name">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="inputEmail4" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="inputEmail4">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="inputPassword4" class="form-label">Password</label>
                                                    <input type="password" class="form-control" id="inputPassword4">
                                                </div>
                                                <div class="col-12">
                                                    <label for="inputAddress" class="form-label">Address</label>
                                                    <input type="text" class="form-control" id="inputAddress"
                                                        placeholder="1234 Main St">
                                                </div>
                                                <div class="col-12">
                                                    <label for="inputAddress2" class="form-label">Address 2</label>
                                                    <input type="text" class="form-control" id="inputAddress2"
                                                        placeholder="Apartment, studio, or floor">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="inputCity" class="form-label">City</label>
                                                    <input type="text" class="form-control" id="inputCity">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="inputState" class="form-label">State</label>
                                                    <select id="inputState" class="form-select">
                                                        <option selected>Choose...</option>
                                                        <option>...</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label for="inputZip" class="form-label">Zip</label>
                                                    <input type="text" class="form-control" id="inputZip">
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="gridCheck3">
                                                        <label class="form-check-label" for="gridCheck3">
                                                            Check me out
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <button type="submit" class="btn btn-primary">Sign in</button>
                                                </div>
                                            </form> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-6 -->

                            <!-- Start:: row-7 -->
                            <div class="row">
                                <div class="col-xl-4">
                                    <div class="card custom-card contactus-form overflow-hidden">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Contact Us Form
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-xl-12 rounded-3">
                                                    <div class="row gy-2">
                                                        <div class="col-xl-6">
                                                            <label for="contact-address-firstname" class="form-label ">First Name :</label>
                                                            <input type="text" class="form-control" id="contact-address-firstname" placeholder="Enter Name">
                                                        </div>
                                                        <div class="col-xl-6">
                                                            <label for="contact-address-lastname" class="form-label ">Last Name :</label>
                                                            <input type="text" class="form-control" id="contact-address-lastname" placeholder="Enter Name">
                                                        </div>
                                                        <div class="col-xl-6 mt-4">
                                                            <label for="contact-address-phone" class="form-label ">Phone No :</label>
                                                            <input type="text" class="form-control" id="contact-address-phone" placeholder="Enter Phone No">
                                                        </div>
                                                        <div class="col-xl-6 mt-4">
                                                            <label for="contact-address-email" class="form-label ">Email Id :</label>
                                                            <input type="email" class="form-control" id="contact-address-email" placeholder="Enter Email Id">
                                                        </div>
                                                        <div class="col-xl-12 mt-4">
                                                            <label for="contact-address-address" class="form-label ">Address :</label>
                                                            <textarea class="form-control" id="contact-address-address" rows="2"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-4">
                                                        <button class="btn btn-primary btn-wave">Send Message</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-7 -->

                        </div>
                    </div>
                    <!-- APP-CONTENT CLOSE -->

@endsection

@section('scripts')
  

@endsection