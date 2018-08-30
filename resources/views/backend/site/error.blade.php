@extends('backend.layouts.main')
@section('title', 'Error')
@section('content')
<section class="content">

  <div class="error-page">
    <h2 class="headline text-red">{{isset($errorCode) ? $errorCode : '500'}}</h2>

    <div class="error-content">
      <h3><i class="fa fa-warning text-red"></i> {{isset($msg) ? $msg : 'Oops! Something went wrong.'}}</h3>

      <p>
        We will work on fixing that right away.
        Meanwhile, you may <a href="{{route('backend.site.index')}}">return to dashboard</a> or try using the search form.
      </p>

      <form class="search-form">
        <div class="input-group">
          <input type="text" name="search" class="form-control" placeholder="Search">

          <div class="input-group-btn">
            <button type="submit" name="submit" class="btn btn-danger btn-flat"><i class="fa fa-search"></i>
            </button>
          </div>
        </div>
        <!-- /.input-group -->
      </form>
    </div>
  </div>
  <!-- /.error-page -->

</section>
@stop