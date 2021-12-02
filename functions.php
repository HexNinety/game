<?php
/**
 * Simplistic HTML templating script.
 */
declare( strict_types = 1 );

date_default_timezone_set( 'UTC' );

/**
 * Templatized output of the <head> element.
 */
function head ( array $args = [] ) {
    $defaults = [
        'title'  => 'Hex90 Game',
        'styles' => 'style.css',
    ];
    $args = array_merge( $defaults, $args );
?>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php print htmlentities( $args['title'], ENT_QUOTES, 'UTF-8' ); ?></title>
<link rel="stylesheet" href="<?php print htmlentities( $args['styles'], ENT_QUOTES, 'UTF-8' ) ;?>" />
<?php
}

/**
 * Make random game text substitutions for various time stamps.
 */
function recentTimeRecursive ( string $input ) {
    $count = null;
    $input = preg_replace( '/__RECENT_TIME__/',
        date( 'Y-m-d H:i',
            strtotime('-' . random_int( 1, 59 ) . ' hour ' . random_int( 1, 59 ) . ' minutes ' . random_int( 1, 59 ). ' seconds')
        ),
    $input, 1, $count);
    return (0 === $count) ? $input : recentTimeRecursive( $input );
}

/**
 * Performs some basic substitutions for text in the game.
 */
function gameText ( string $text = '', array $challenge = [], string $user_flag = '' ) {
    $static_substitution_map = [
        "__BASE_URL__" => "http://{$_SERVER['HTTP_HOST']}",
        "__HTTP_DOMAIN__" => str_replace(':' . $_SERVER['SERVER_PORT'], '', "http://{$_SERVER['HTTP_HOST']}"),
        "__CHALLENGE_PROMPT__" => $challenge['prompt'],
        "__CHALLENGE_TEXT__" => $challenge['text'],
        "__NOW__" => date( 'D M d H:i:s T Y' ),
        "__REMOTE_ADDR__" => $_SERVER['REMOTE_ADDR'],
        "__USER_FLAG__" => $user_flag
    ];
    $search = array_keys( $static_substitution_map );
    $replace = array_values( $static_substitution_map );
    $text = str_replace( $search, $replace, str_replace( $search, $replace, $text ) );

    // Dynamic replacements.
    $text = recentTimeRecursive( $text );
    $text = preg_replace_callback( '/__RANDOM_INT__/', function () {
        return random_int( 10, 99 );
    }, $text );

    return $text;
}
