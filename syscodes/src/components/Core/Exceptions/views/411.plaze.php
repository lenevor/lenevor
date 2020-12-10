<@extends('errors::layout')

<@section('title', $exception->getTitle())  {{-- Title: Length Required --}}
<@section('code', $exception->getStatusCode())  {{-- Code: 411 --}} 
<@section('message', $exception->getMessage())
