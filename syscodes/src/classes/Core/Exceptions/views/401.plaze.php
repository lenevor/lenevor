<@extends('errors::layout')

<@section('title', $exception->getTitle())  {{-- Title: Unauthorized --}}
<@section('code', $exception->getStatusCode())  {{-- Code: 401 --}} 
<@section('message', $exception->getMessage())
