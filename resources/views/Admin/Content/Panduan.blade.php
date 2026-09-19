@extends('Admin.Layout._layout')

@section('title', 'Manajemen Panduan')

@section('content')
  @include('Admin.Content.EducationIndex', ['contentType' => 'Panduan', 'items' => $items])
@endsection
