<@extends('errors::layout')

<@section('title', $exception->getTitle())  {{-- Title: Precondition Required --}}
<@section('code', $exception->getStatusCode())  {{-- Code: 428 --}} 
<@section('message', $exception->getMessage())
