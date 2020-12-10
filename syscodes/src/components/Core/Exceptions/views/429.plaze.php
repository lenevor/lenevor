<@extends('errors::layout')

<@section('title', $exception->getTitle())  {{-- Title: Too Many Requests --}}
<@section('code', $exception->getStatusCode())  {{-- Code: 429 --}} 
<@section('message', $exception->getMessage())
