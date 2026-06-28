@extends('layouts.app', ['title' => 'Results'])

@section('content')
<livewire:results-page :permalink_token="$permalink_token" />
@endsection
