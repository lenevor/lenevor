<@extends('errors::layout')

<@section('title', $exception->getTitle())  {{-- Title: Not Found --}}
<@section('code', $exception->getStatusCode())  {{-- Code: 404 --}} 
<@section('message', $exception->getMessage())
