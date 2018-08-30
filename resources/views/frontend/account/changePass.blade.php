@extends('frontend.layouts.layout')
@section('title', 'Change password')
@section('page_header')
<!-- Page Header -->
<div class="page-header bg-dark text-center">
	<div class="container">
		<div class="row">
			<div class="col-xs-12">
				<!-- Page Header Wrapper -->
				<div class="page-header-wrapper">
					<!-- Title & Sub Title -->
					<h3 class="title">Change password</h3>
					<h6 class="sub-title"></h6>
				</div><!-- Page Header Wrapper -->
			</div><!-- Coloumn -->
		</div><!-- Row -->
	</div><!-- Container -->
</div>
<!-- /Page Header -->
@stop
@section('content')
<section class="bg-grey">
	<div class="container">
		<div class="row">
			<div class="col-md-offset-3 col-md-6">
				<div class="title-container text-left sm">
					<div class="title-wrap">
						<h4 class="title">Change password</h4>
						<span class="separator line-separator"></span>
					</div>							
				</div><!-- Name -->
				@if(\Session::has('msg_changePass'))
				<div class="alert alert-success text-center" role="alert">
					<p><strong>{{\Session::get('msg_changePass')}}</strong></p>
				</div>
				@endif
				<!-- Form Begins -->
				<form action="{{route('frontend.account.pChangePass')}}" method="POST">
					{{csrf_field()}}
					<!-- Field 1 -->
					<div class="input-text form-group {{$errors->has('oldPass') ? 'has-error' : ''}}">
						<label class="required">Current password</label>
						<input type="password" name="oldPass" class="input-name form-control" placeholder="Current password" required maxlength="255" />
						<span class="help-block">{{$errors->first('oldPass')}}</span>
					</div>
					<!-- Field 1 -->
					<div class="input-text form-group {{$errors->has('newPass') ? 'has-error' : ''}}">
						<label class="required">New password</label>
						<input type="password" name="newPass" class="input-name form-control" placeholder="New password" required maxlength="255" />
						<span class="help-block">{{$errors->first('newPass')}}</span>
					</div>
					<div class="input-text form-group {{$errors->has('newPass_confirmation') ? 'has-error' : ''}}">
						<label class="required">Confirm password</label>
						<input type="password" name="newPass_confirmation" class="input-name form-control" placeholder="Confirm password" required maxlength="255" />
						<span class="help-block">{{$errors->first('newPass_confirmation')}}</span>
					</div>
					<!-- Button -->
					<button class="btn btn-block" type="submit">Update</button>
				</form>
			</div><!-- Column -->
		</div><!-- Row -->
	</div><!-- Container -->
</section>
@stop