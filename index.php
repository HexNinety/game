<?php
/**
 * Hex90 - Game entrypoint script.
 *
 * This is the main entrypoint for the Hex90 game interface.
 */
require_once __DIR__ . '/functions.php';
$CONFIG            = parse_ini_file( __DIR__ . '/config.ini.php', true, INI_SCANNER_TYPED);
$challenges        = $CONFIG['challenges'];

$current_challenge = ( isset( $_REQUEST['puzzle'] ) && array_key_exists( $_REQUEST['puzzle'], $challenges ) )
    ? $_REQUEST['puzzle']
    : 0;

$game_text         = $challenges[$current_challenge]['text'];
$puzzle_name       = $challenges[$current_challenge]['name'];
$game_prompt       = ( isset( $challenges[$current_challenge]['prompt'] ) )
    ? $challenges[$current_challenge]['prompt'] : $CONFIG['prompt'];
$game_flags        = explode( '||', $challenges[$current_challenge]['flag'] );

// Each time we load the page, we see if the answer to the current
// challenge is correct.
if ( isset( $_POST['flag'] ) && in_array( $_POST['flag'], $game_flags) ) {
    // If the player got the flag, proceed to the next challenge and
    // reset the game variables.
    foreach ( $challenges[$current_challenge] as $k => $v ) {
        if ( 'next:' === substr( $k, 0, 5 ) && $_POST['flag'] === substr( $k, 5 ) ) {
            $current_challenge = $challenges[$current_challenge][$k];
            break;
        }
    }
    $game_text         = $challenges[$current_challenge]['text'];
    $puzzle_name       = $challenges[$current_challenge]['name'];
    $game_prompt       = ( isset( $challenges[$current_challenge]['prompt'] ) )
        ? $challenges[$current_challenge]['prompt'] : $CONFIG['prompt'];
} else if ( isset( $_POST['flag'] ) ) {
    // Otherwise, replace game text with the indicator it was incorrect.
    $game_text = ( $challenges[$current_challenge]['retry_text'] )
        ? $challenges[$current_challenge]['retry_text']
        : $CONFIG['retry_text'][ array_rand( $CONFIG['retry_text'] ) ];
}

// Certain flags are special in that they produce a specific game text.
if ( isset( $_POST['flag'] ) && array_key_exists( $_POST['flag'], $CONFIG['commands'] ) ) {
    $game_text   = $CONFIG['commands'][$_POST['flag']];
}
?>
<!DOCTYPE html>
<html lang="en">
<?php head([
    'title' => htmlentities( $puzzle_name, ENT_QUOTES, 'UTF-8' ) . ' - Hex90 Game'
]); ?>
<body>
<!--
<?php if ( true === $CONFIG['debug'] ) { var_dump( $_REQUEST ); var_dump( $current_challenge ); var_dump( $game_text ); } ?>
-->
    <div id="game-container">
        <div id="game-screen"></div>
        <div id="player-text">
            <div id="player-prompt"></div>
            <form action="<?php print $_SERVER['PHP_SELF']; ?>?puzzle=<?php print htmlentities( $current_challenge, ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                <input id="flag" name="flag"
                    value=""
                    placeholder="<?php print htmlentities( $challenges[$current_challenge]['placeholder'], ENT_QUOTES, 'UTF-8' ); ?>"
                    spellcheck="false"
                />
            </form>
        </div>
    </div>

    <script id="game-text" type="text/plain"><?php print preg_replace( '/<\/script(\t|\n|\f|\r| |>|\/)/i', '< /script$1', gameText( $game_text, $challenges[$current_challenge], ( isset( $_POST['flag'] ) ? $_POST['flag'] : '') ) ); // See https://html.spec.whatwg.org/multipage/syntax.html#cdata-rcdata-restrictions ?></script>
    <script id="game-prompt" type="text/plain"><?php print preg_replace( '/<\/script(\t|\n|\f|\r| |>|\/)/i', '< /script$1', $game_prompt ); ?></script>
    <script src="main.js"></script>
    <script>hex90.play(<?php
        print ( true === $CONFIG['debug'] )
            ? 1
            : ( isset( $challenges[$current_challenge]['speed'] ) )
                ? $challenges[$current_challenge]['speed'] : $CONFIG['speed'];
    ?>);</script>
</body>
</html>
