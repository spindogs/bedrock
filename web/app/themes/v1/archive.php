<?php
use Timber\Timber;
use Timber\Post;
use Timber\PostQuery;

$data = Timber::get_context();
$data['post'] = new Post();
$data['posts'] = new PostQuery();
Timber::render('archive.twig', $data);
