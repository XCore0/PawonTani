@extends('Admin.Layout._layout')

@section('title', 'Manajemen Tips')

@section('content')
  @include('Admin.Content.EducationIndex', ['contentType' => 'Tips', 'items' => $items])
@endsection
