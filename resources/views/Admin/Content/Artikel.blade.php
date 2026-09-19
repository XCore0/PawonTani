@extends('Admin.Layout._layout')

@section('title', 'Manajemen Artikel')

@section('content')
  @include('Admin.Content.EducationIndex', ['contentType' => 'Artikel', 'items' => $items])
@endsection
