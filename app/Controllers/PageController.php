<?php
declare(strict_types=1);

namespace App\Controllers;
use App\Core\Controller;


final class PageController extends Controller
{
	public function homepage(): string
	{
	    return $this->render('pages/homepage');
	}

	public function terms(): string
	{
	    return $this->render('pages/terms');
	}

	public function privacy(): string
	{
	    return $this->render('pages/privacy');
	}

	public function cookies(): string
	{
	    return $this->render('pages/cookies');
	}
}