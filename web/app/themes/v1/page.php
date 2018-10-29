<?php
use Timber\Timber;
use Timber\Post;

$data = Timber::get_context();
$data['post'] = new Post();
Timber::render('page.twig', $data);
