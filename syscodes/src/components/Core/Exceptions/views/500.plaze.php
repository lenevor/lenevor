<@extends('errors::layout')

<@section('title', $exception->getTitle())  {{-- Title: Internal Server Error --}}
<@section('code', $exception->getStatusCode())  {{-- Code: 500 --}} 
<@section('message', $exception->getMessage())
