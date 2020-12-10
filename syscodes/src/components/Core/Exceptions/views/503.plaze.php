<@extends('errors::layout')

<@section('title', $exception->getTitle())  {{-- Title: Service Unavailable --}}
<@section('code', $exception->getStatusCode())  {{-- Code: 503 --}} 
<@section('message', $exception->getMessage())
