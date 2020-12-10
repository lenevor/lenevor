<@extends('errors::layout')

<@section('title', $exception->getTitle())  {{-- Title: Unsupported Media Type --}}
<@section('code', $exception->getStatusCode())  {{-- Code: 415 --}} 
<@section('message', $exception->getMessage())
