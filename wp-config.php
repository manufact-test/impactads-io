<?php
/**
 * WordPress config — impactads.io (Hostinger)
 */
define( 'DB_NAME', 'u218517330_7Hmz2' );
define( 'DB_USER', 'u218517330_qVCJ0' );
define( 'DB_PASSWORD', 'boOSGPXDVW' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',         'Iebh83Pt9F_};vFLmJuF42<-iFI:/,x&.7NAPUww03&wQJRae]+SxS/-Pn7a#=!?' );
define( 'SECURE_AUTH_KEY',  '~P!&{e<9_{6A+?L*@0M71&YNjNx.p(?ZRxlREXH#~S9b0.VBLSRO|+t*Qko`^6<4' );
define( 'LOGGED_IN_KEY',    's3a1Be1V/o@}nxK*Av;4GGbd|ZZG,41fRj*L8{s7Fe?i_=9TIuGXXgtfxzV?V`6B' );
define( 'NONCE_KEY',        ']k,gM~qK8}1L=WG~O{=iVIYjPlr~<)y~`zpnQVp&Zh$`*CmvHc%/fuN(4{TBNE=y' );
define( 'AUTH_SALT',        '~~fCdkem=Q`tMoK]=L-45,@O{c]&L2%&m^Uw FK2KJb51,AS_}=80KlS:?{TpEum' );
define( 'SECURE_AUTH_SALT', 'XA|j*uC~0sA1):G3^EQ?DiYB{r03FBO(I(We%?:AUj/&H|J;luWe#4~zf/WT%42t' );
define( 'LOGGED_IN_SALT',   '7(C/oCkL>@t!`Zg8^J{m[IoyG^Zc[L&BR!#TQS%+9jju$}SH=O2|g:qomW<?`-$!' );
define( 'NONCE_SALT',       'T]W!L$96W9e>AJt$W[|LiQm](.02kU00i]+sVLUQZc*ft-b&S|U$U=8J[NJlGGsX' );

$table_prefix = 'wp_';

define( 'WP_HOME', 'https://impactads.io' );
define( 'WP_SITEURL', 'https://impactads.io' );
define( 'WP_DEBUG', false );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
