<?php
/*
 * PHP class to provide dropdown list of organisation/group types
 * Subversion repository: https://damssupport.bodleian.ox.ac.uk/svn/impact/php
 * Author: Sushila Burgess
 *
 */

require_once 'lookup_table.php';

class Nisba extends Lookup_Table {

  #------------
  # Properties 
  #------------

  #-----------------------------------------------------

  function Nisba( &$db_connection ) { 

    $this->project = new Project( $db_connection );
    $table_name = $this->project->proj_nisba_tablename();

    $this->Lookup_Table( $db_connection, 
                         $lookup_table_name = $table_name, 
                         $id_column_name    = 'nisba_id', 
                         $desc_column_name  = 'nisba_desc' );

    $this->get_all_possible_nisbas();

    $this->location_obj = new Popup_Location( $this->db_connection );
  }
  #-----------------------------------------------------

  function write_extra_fields2_new() {

    html::tabledata_start( 'colspan="2"' );

    html::italic_start();
    echo 'A location may optionally be associated with the nisba:';
    html::italic_end();
    echo LINEBREAK;

    $this->place_entry_field( $location_id = NULL );

    html::tabledata_end();

    html::new_tablerow();
  }
  #-----------------------------------------------------

  function write_extra_fields2_existing( $id_value = NULL ) {

    if( ! $id_value ) return;

    $statement = 'select location_id from ' . $this->location_obj->proj_nisba_tablename() 
               . " where nisba_id = $id_value ";
    $location_id = $this->db_select_one_value( $statement );

    html::tabledata_start( 'colspan="2"' );

    $this->place_entry_field( $location_id, $id_value );

    html::tabledata_end();
    html::new_tablerow();
  }
  #----------------------------------------------------- 

  function place_entry_field( $location_id = NULL, $nisba_id = NULL ) {

    $fieldset_name = $this->get_place_entry_fieldset_name();
    $decode_field_label = 'Location';

    if( $nisba_id ) {
      $calling_field = $this->location_obj->proj_id_fieldname_from_fieldset_name( $fieldset_name, $nisba_id );
    }
    else {
      $calling_field = $this->location_obj->proj_new_id_fieldname_from_fieldset_name( $fieldset_name );
    }

    $decode_fieldname = $this->location_obj->proj_decode_fieldname_from_id_fieldname( $calling_field );
    $decode_field_initial_value = 'Select or create ' . strtolower( $decode_field_label );

    $calling_field_value = NULL;
    if( $location_id ) {
      $calling_field_value = $location_id;
      $decode_field_initial_value = $this->location_obj->proj_get_description_from_id( $location_id );
    }

    $this->location_obj->proj_input_fields_calling_popups( 
                           $calling_form = $this->form_name, $calling_field,
                           $decode_fieldname, 
                           $decode_field_label, 
                           $decode_field_initial_value,
                           NULL, NULL, $calling_field_value ); 

    if( $location_id ) {
      $parms = 'onclick="document.' . $this->form_name . '.' . $calling_field    . ".value='';"
                      . 'document.' . $this->form_name . '.' . $decode_fieldname . ".value='';" . '"';
      html::button( 'clear_' . $fieldset_name . '_button', 'X', $tabindex=1, $parms );
      echo ' (Click to blank out ' . strtolower( $decode_field_label )
           . ' on screen, then Save to finalise.)';
    }
  }
  #-----------------------------------------------------
  function get_place_entry_fieldset_name() {
    return 'nisba_location';
  }
  #-----------------------------------------------------

  function find_uses_of_this_id( $id_value = NULL ) {  # overrides parent class

    if( ! $id_value ) $id_value = $this->read_post_parm( $this->id_column_name );
    if( ! $id_value ) $this->die_on_error( 'No ID value passed to method "Find uses of this ID".' );

    $uses = NULL;
    $this->referencing_class = 'person';
    $this->referencing_method = 'one_person_search_results';
    $this->referencing_id_column = 'iperson_id';

    $statement = 'select p.iperson_id, p.foaf_name '
               . ' from ' . $this->project->proj_person_tablename() . ' p, ' 
                          . $this->project->proj_relationship_tablename() . ' rel'
               . " where rel.left_table_name = '" . $this->project->proj_person_tablename() . "' "
               . ' and p.person_id = rel.left_id_value'
               . " and rel.relationship_type = '" . RELTYPE_PERSON_MEMBER_OF_NISBA . "' "
               . " and rel.right_table_name = '" . $this->project->proj_nisba_tablename() . "' "
               . " and rel.right_id_value = '$id_value'";

    $statement = $statement . ' order by iperson_id';

    $uses = $this->db_select_into_array( $statement );

    if( $uses ) $this->lookup_reference_column_labels = array( 'iperson_id' => 'Person ID',
                                                               'description' => 'Name of person' );
    return $uses;
  }
  #-----------------------------------------------------

  function clear() {

    parent::clear();
    $this->get_all_possible_nisbas();
  }
  #----------------------------------------------------------------------------------

  function get_all_possible_nisbas() {

    $this->all_possible_nisbas = array();

    $statement = 'select * from ' . $this->project->proj_nisba_tablename() . ' order by nisba_desc';
    $nisbas = $this->db_select_into_array( $statement );
    
    if( count( $nisbas ) > 0 ) {
      foreach( $nisbas as $row ) {
        extract( $row, EXTR_OVERWRITE );

        if( $location_id ) {
          $query_string = '?class_name=location&method_name=one_location_search_results&location_id=' . $location_id;

          $func = $this->project->proj_database_function_name( 'link_to_edit_app' );
          $statement = "select $func ( '" . $this->escape ( $nisba_desc ) . "', "
                     . "'" . $this->escape( $query_string ) . "' ) ";
          $nisba_desc = $this->db_select_one_value( $statement );
        }
        $this->all_possible_nisbas[ $nisba_id ] = $nisba_desc;
      }
    }
  }
  #----------------------------------------------------------------------------------

  function get_nisbas_of_person( $person_id = NULL ) {

    $nisbas_of_person = array();
    if( ! $person_id ) # could be a new record
      return $nisbas_of_person;

    $statement = 'select right_id_value as nisba_id '
               . ' from ' . $this->project->proj_relationship_tablename()
               . " where left_table_name = '" . $this->project->proj_person_tablename() . "' "
               . " and left_id_value = '$person_id' "
               . " and relationship_type = '" . RELTYPE_PERSON_MEMBER_OF_NISBA . "' "
               . " and right_table_name = '" . $this->project->proj_nisba_tablename() . "'"
               . ' order by nisba_id';

    $nisba_rows = $this->db_select_into_array( $statement );

    if( count( $nisba_rows ) > 0 ) {
      foreach( $nisba_rows as $row ) {
        extract( $row, EXTR_OVERWRITE );
        $nisbas_of_person[] = $nisba_id;
      }
    }

    return $nisbas_of_person;
  }
  #----------------------------------------------------------------------------------


  function nisba_entry_fields( $person_id = NULL ) {

    $nisbas_in_use = $this->get_nisbas_of_person( $person_id );

    $this->project->proj_multiple_compact_checkboxes( 
                      $all_possible_ids_and_descs = $this->all_possible_nisbas,
                      $selected_ids = $nisbas_in_use,
                      $checkbox_fieldname = 'nisba_chkbox',
                      $list_label = '' );
  }
  #-----------------------------------------------------

  function save_nisbas( $person_id ) {

    if( ! $person_id ) die( 'Invalid input while saving nisba of person.' );

    $nisbas_of_person = $this->get_nisbas_of_person( $person_id );

    $statement = 'select max(nisba_id) from ' . $this->project->proj_nisba_tablename();
    $max_nisba_id = $this->db_select_one_value( $statement );

    $nisba_id_string = '';

    $this->rel_obj = new Relationship( $this->db_connection );

    for( $i = 1; $i <= $max_nisba_id; $i++ ) {
      $nisba_id = $i;
      $existing_nisba = FALSE;
      if( in_array( $nisba_id, $nisbas_of_person )) $existing_nisba = TRUE;

      $fieldname = 'nisba_chkbox' . $nisba_id;
      if( $this->parm_found_in_post( $fieldname )) {
        if( ! $existing_nisba ) {

          $this->rel_obj->insert_relationship( $left_table_name = $this->project->proj_person_tablename(),
                                               $left_id_value = $person_id,
                                               $relationship_type = RELTYPE_PERSON_MEMBER_OF_NISBA,
                                               $right_table_name = $this->project->proj_nisba_tablename(),
                                               $right_id_value = $nisba_id );
        }
      }
      else { # checkbox was not ticked for this nisba
        if( $existing_nisba ) {

          $this->rel_obj->delete_relationship( $left_table_name = $this->project->proj_person_tablename(),
                                               $left_id_value = $person_id,
                                               $relationship_type = RELTYPE_PERSON_MEMBER_OF_NISBA,
                                               $right_table_name = $this->project->proj_nisba_tablename(),
                                               $right_id_value = $nisba_id );
        }
      }
    }
  }
  #-----------------------------------------------------

  function get_label_for_desc_field() { # overrides parent method from 'lookup table'
    return 'Nisba';
  }
  #----------------------------------------------------- 

  function get_label_for_change_button() { # override this in the child method if required
    return 'Save';
  }
  #----------------------------------------------------- 

  function get_extra_insert_cols() { # override if required
    return 'location_id, ';
  }
  #----------------------------------------------------- 

  function get_extra_insert_vals() { # override if required

    $location_val = 'null, ';

    $fieldset = $this->get_place_entry_fieldset_name();  # nisba location
    $new_fieldname = $this->location_obj->proj_new_id_fieldname_from_fieldset_name( $fieldset );

    if( $this->parm_found_in_post( $new_fieldname )) {
      $location_val = $this->read_post_parm( $new_fieldname );
      $location_val .= ', ';
    }

    return $location_val;
  }
  #----------------------------------------------------- 

  function get_extra_update_cols_and_vals() { # override if required

    $location_val = 'null';

    $fieldset = $this->get_place_entry_fieldset_name();  # nisba location
    $fieldname_start = $this->location_obj->proj_id_fieldname_from_fieldset_name( $fieldset );
    foreach( $_POST as $parm_name => $parm_value ) {
      if( $this->string_starts_with( $parm_name, $fieldname_start )) {
        $the_rest = substr( $parm_name, strlen( $fieldname_start ));
        if( $this->is_integer( $the_rest )) { # fieldname is in correct format
          $location_val = $this->read_post_parm( $parm_name ); # go through proper validation
          if( $location_val == '' ) $location_val = 'null';
          break;
        }
      }
    }

    return "location_id = $location_val, ";
  }
  #----------------------------------------------------- 

  function validate_parm( $parm_name ) {  # overrides parent method

    switch( $parm_name ) {

      case 'nisba_id':
        return $this->is_integer( $this->parm_value );

      case 'nisba_desc':
        return $this->is_ok_free_text( $this->parm_value );
        
      default:
        #........................................................................................................

        # Check for nisba entry for a person
        $fieldstart = 'nisba_chkbox';
        if( $this->string_starts_with( $parm_name, $fieldstart )) {
          $the_rest = substr( $parm_name, strlen( $fieldstart ));
          if( $this->is_integer( $the_rest ))   # e.g. if fieldname is 'nisba_chkbox1', value should be '1'
            if( intval( $the_rest ) == intval( $this->parm_value )) return TRUE;
        }
        #........................................................................................................

        # Now check for location entry fields for a nisba
        $fieldset = $this->get_place_entry_fieldset_name();  # nisba location

        $new_fieldname            = $this->location_obj->proj_new_id_fieldname_from_fieldset_name( $fieldset );
        $existing_fieldname_start = $this->location_obj->proj_id_fieldname_from_fieldset_name( $fieldset );

        if( $parm_name == $new_fieldname ) {
          return $this->is_integer( $this->parm_value );
        }
        elseif( $this->string_starts_with( $parm_name, $existing_fieldname_start )) {
          $the_rest = substr( $parm_name, strlen( $existing_fieldname_start ));
          if( $this->is_integer( $the_rest )) {  # fieldname consists of fieldset name plus nisba ID
            return $this->is_integer( $this->parm_value ); # parm value should be a location ID
          }
        }
        #........................................................................................................

        return parent::validate_parm( $parm_name );
    }
  }
  #-----------------------------------------------------

}
?>
