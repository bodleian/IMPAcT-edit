<?php
/*
 * PHP class to provide dropdown list of work types
 * Subversion repository: https://damssupport.bodleian.ox.ac.uk/svn/impact/php
 * Author: Sushila Burgess
 *
 */

define( 'DEFAULT_WORK_TYPE', 1 ); # just go for first on list (for Impact, this should be 'Epistle')

require_once 'lookup_table.php';

class Work_Type extends Lookup_Table {

  #------------
  # Properties 
  #------------

  #-----------------------------------------------------

  function Work_Type( &$db_connection ) { 

    $project = new Project( $db_connection );
    $table_name = $project->proj_work_type_tablename();
    $project = NULL;

    $this->Lookup_Table( $db_connection, 
                         $lookup_table_name = $table_name, 
                         $id_column_name    = 'work_type_id', 
                         $desc_column_name  = 'work_type_desc' );
  }
  #-----------------------------------------------------
  # Override parent method so can set default work type.

  function lookup_table_dropdown( $field_name = NULL, 
                                  $field_label = NULL, 
                                  $selected_id = NULL ) {

    if( ! $field_name ) $field_name = 'work_type';
    if( ! $selected_id ) $selected_id = DEFAULT_WORK_TYPE;

    parent::lookup_table_dropdown( $field_name, $field_label, $selected_id );
  }
  #----------------------------------------------------- 

  function is_composite_type( $type_id ) {

    $statement = "select is_composite_work from $this->lookup_table_name where work_type_id = $type_id";
    $is_composite_type = $this->db_select_one_value( $statement );
    if( $is_composite_type == 1 ) return TRUE;
    return FALSE;
  }
  #----------------------------------------------------- 

  function edit_lookup_table2() {  # overrides parent class

    parent::edit_lookup_table2(); # Actually do the addition, update or deletion

    # Now see if it was an update. If so, update work description and decode field in queryable work.
    $updated = TRUE;

    $non_update_buttons = array( 'add_button',
                                 'check_deletion_button',
                                 'delete_button',
                                 'cancel_deletion_button' );

    foreach( $non_update_buttons as $button ) {
      if( $this->parm_found_in_post( $button )) {
        $updated = FALSE;
        break;
      }
    }

    if( $updated ) {  # Refresh work description and decode of work type in the 'queryable work' table.
      $id_value = $this->read_post_parm( $this->id_column_name );
      $desc = $this->read_post_parm( $this->desc_column_name );

      $proj = new Project( $this->db_connection );
      $work_table = $proj->proj_work_tablename();
      $queryable_work_table = $proj->proj_queryable_work_tablename();
      $refresh_func = $proj->proj_database_function_name( 'get_work_desc', 
                                                          $include_collection_code = TRUE );
      $proj = NULL;

      $statement = "update $work_table set description = $refresh_func ( work_id )"
                 . " where work_type = $id_value";
      $this->db_run_query( $statement );

      $statement = "update $queryable_work_table set type_of_work = '" . $this->escape( $desc ) . "', "
                 . " description = $refresh_func ( work_id )"
                 . " where iwork_id in ( select iwork_id from $work_table where work_type = $id_value )";
      $this->db_run_query( $statement );
    }
  }
  #----------------------------------------------------- 


  function find_uses_of_this_id( $id_value = NULL ) {  # overrides parent class

    if( ! $id_value ) $id_value = $this->read_post_parm( $this->id_column_name );
    if( ! $id_value ) $this->die_on_error( 'No ID value passed to method "Find uses of this ID".' );

    $uses = NULL;
    $this->referencing_class = PROJ_COLLECTION_WORK_CLASS;
    $this->referencing_method = 'edit_work';
    $this->referencing_id_column = 'iwork_id';

    $proj = new Project( $this->db_connection );

    $statement = 'select w.iwork_id, w.description '
               . ' from ' . $proj->proj_work_tablename() . ' w, ' . $proj->proj_work_type_tablename() . ' lookup '
               . ' where lookup.work_type_id = w.work_type '
               . ' and lookup.work_type_id = ';

    $statement = $statement . $id_value;
    $statement = $statement . ' order by iwork_id';

    $uses = $this->db_select_into_array( $statement );

    if( $uses ) $this->lookup_reference_column_labels = array( 'iwork_id' => 'Work ID',
                                                               'description' => 'Description' );
    return $uses;
  }
  #-----------------------------------------------------

}
?>
