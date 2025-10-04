@extends('layouts.app')

@section('content')
    <livewire:contact.contacts-list />
    <livewire:contact.contact-form />
    <livewire:integration.connect-account wire:id="connect-account-component" />
@endsection
