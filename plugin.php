<?php defined( 'App' ) or die( 'BoidCMS' );
/**
 *
 * Order By – Effortlessly Organize Your Post Order
 *
 * @package Plugin_Order_By
 * @author Shuaib Yusuf Shuaib
 * @version 0.1.0
 */

if ( 'order-by' !== basename( __DIR__ ) ) return;

global $App;
$App->set_action( 'install', 'order_by_install' );
$App->set_action( 'uninstall', 'order_by_uninstall' );
$App->set_action( 'recent_posts', 'order_by_apply', 11 );
$App->set_action( 'admin', 'order_by_admin' );

/**
 * Initialize Order By, first time install
 * @param string $plugin
 * @return void
 */
function order_by_install( string $plugin ): void {
  global $App;
  if ( 'order-by' === $plugin ) {
    $config = array();
    $config[  'sort' ] =      'date';
    $config[ 'order' ] =      'desc';
    $App->set( $config, 'order-by' );
  }
}

/**
 * Free database space, while uninstalled
 * @param string $plugin
 * @return void
 */
function order_by_uninstall( string $plugin ): void {
  global $App;
  if ( 'order-by' === $plugin ) {
    $App->unset( 'order-by' );
  }
}

/**
 * Apply order
 * @param array $articles
 * @return array
 */
function order_by_apply( array $articles ): array {
  $callback = order_by_callback();
  uasort(  $articles, $callback );
  return $articles;
}

/**
 * Admin settings
 * @return void
 */
function order_by_admin(): void {
  global $App, $layout, $page;
  switch ( $page ) {
    case 'order-by':
      $config = $App->get( 'order-by' );
      $layout[ 'title' ] = 'Order By';
      $layout[ 'content' ] = '
      <form action="' . $App->admin_url( '?page=order-by', true ) . '" method="post">
        <label for="sort" class="ss-label">Field to Sort by</label>
        <select id="sort" name="sort" class="ss-select ss-mobile ss-w-6 ss-mx-auto">
          <option value="date"' . ( 'date' === $config[ 'sort' ] ? ' selected' : '' ) . '>Date</option>
          <option value="title"' . ( 'title' === $config[ 'sort' ] ? ' selected' : '' ) . '>Title</option>
        </select>
        <label for="order" class="ss-label">Sorting Order</label>
        <select id="order" name="order" class="ss-select ss-mobile ss-w-6 ss-mx-auto">
          <option value="asc"' . ( 'asc' === $config[ 'order' ] ? ' selected' : '' ) . '>Ascending (ASC)</option>
          <option value="desc"' . ( 'desc' === $config[ 'order' ] ? ' selected' : '' ) . '>Descending (DESC)</option>
        </select>
        <input type="hidden" name="token" value="' . $App->token() . '">
        <input type="submit" name="save" value="Save" class="ss-btn ss-mobile ss-w-5">
      </form>';
      if ( isset( $_POST[ 'save' ] ) ) {
        $App->auth();
        $config[ 'sort' ] = $App->esc( $_POST[ 'sort' ] ?? $config[ 'sort' ] );
        $config[ 'order' ] = $App->esc( $_POST[ 'order' ] ?? $config[ 'order' ] );
        if ( $App->set( $config, 'order-by' ) ) {
          $App->alert( 'Settings saved successfully.', 'success' );
          $App->go( $App->admin_url( '?page=order-by' ) );
        }
        
        $App->alert( 'Failed to save settings, please try again.', 'error' );
        $App->go( $App->admin_url( '?page=order-by' ) );
      }
      
      require_once $App->root( 'app/layout.php' );
      break;
  }
}

/**
 * Order by date in ASC
 * @param array $first
 * @param array $second
 * @return int
 */
function order_by_date_asc( array $first, array $second ): int {
  return ( strtotime( $first[ 'date' ] ) <=> strtotime( $second[ 'date' ] ) );
}

/**
 * Order by date in DESC
 * @param array $first
 * @param array $second
 * @return int
 */
function order_by_date_desc( array $first, array $second ): int {
  return ( strtotime( $second[ 'date' ] ) <=> strtotime( $first[ 'date' ] ) );
}

/**
 * Order by title in ASC
 * @param array $first
 * @param array $second
 * @return int
 */
function order_by_title_asc( array $first, array $second ): int {
  return strcmp( $first[ 'title' ], $second[ 'title' ] );
}

/**
 * Order by title in DESC
 * @param array $first
 * @param array $second
 * @return int
 */
function order_by_title_desc( array $first, array $second ): int {
  return strcmp( $second[ 'title' ], $first[ 'title' ] );
}

/**
 * Order callback
 * @return callable
 */
function order_by_callback(): callable {
  global $App;
  $config = $App->get( 'order-by' );
  if ( 'date' === $config[ 'sort' ] ) {
    $callback = 'order_by_date_asc';
    if ( 'asc' !== $config[ 'order' ] ) {
      $callback = 'order_by_date_desc';
    }
  } else {
    $callback = 'order_by_title_asc';
    if ( 'asc' !== $config[ 'order' ] ) {
      $callback = 'order_by_title_desc';
    }
  }
  
  return $callback;
}
?>
