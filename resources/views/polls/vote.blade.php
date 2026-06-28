@extends('layouts.app', ['title' => 'Vote'])

@section('content')
<livewire:voting-page :permalink_token="$permalink_token" :magic_token="$magic_token ?? null" />
@endsection
