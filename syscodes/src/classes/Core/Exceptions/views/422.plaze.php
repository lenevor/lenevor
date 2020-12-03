<@extends('errors::layout')

<@section('title', $exception->getTitle())  {{-- Title: Unprocessable Entity --}}
<@section('code', $exception->getStatusCode())  {{-- Code: 422 --}} 
<@section('message', $exception->getMessage())
