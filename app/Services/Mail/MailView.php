<?php
declare(strict_types=1);

namespace App\Services\Mail;

class MailView
{

	/**
	 * @param string $template
	 * @param array<string, mixed> $data
	 * @param bool $toHtml
	 * @return string
	 */
    public static function render(string $template, array $data = [], $toHtml = false): string
    {
    	  //var_dump($data);
        extract($data);
        


        if ($toHtml === true) {
	        // content
	        ob_start();
	        require __DIR__ . "/../../Views/mails/{$template}.php";
	        $content = ob_get_clean();

            // layout
           ob_start();
           require __DIR__ . "/../../Views/mails/layout.php";
           return ob_get_clean();
       }
       ob_start();
       require __DIR__ . "/../../Views/mails/{$template}.txt.php";
	    $content = ob_get_clean();
       return $content;

    }

	/**
	 * @param string $template
	 * @param array<string, mixed> $data
	 * @return string
	 */
	public static function renderHtml(string $template, array $data = []): string
	{
	    return self::render($template, $data, true);
	}

	/**
	 * @param string $template
	 * @param array<string, mixed> $data
	 * @return string
	 */
	public static function renderText(string $template, array $data = []): string
	{
	    return self::render($template, $data, false);
	}

}