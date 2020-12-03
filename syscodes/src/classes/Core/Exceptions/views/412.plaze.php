<@extends('errors::layout')

<@section('title', $exception->getTitle())  {{-- Title: Precondition Failed --}}
<@section('code', $exception->getStatusCode())  {{-- Code: 412 --}} 
<@section('message', $exception->getMessage())
