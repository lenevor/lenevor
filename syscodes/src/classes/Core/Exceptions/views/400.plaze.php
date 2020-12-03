<@extends('errors::layout')

<@section('title', $exception->getTitle())  {{-- Title: Bad Request --}}
<@section('code', $exception->getStatusCode())  {{-- Code: 400 --}} 
<@section('message', $exception->getMessage())
