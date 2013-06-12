<?php
/*
 * IMPAcT-specific 'manifestation' object
 * Subversion repository: https://damssupport.bodleian.ox.ac.uk/svn/impact/php
 * Author: Sushila Burgess
 *
 */


class Islamic_Manifestation extends Manifestation {

  #----------------------------------------------------------------------------------

  function Islamic_Manifestation( &$db_connection ) {

    #-----------------------------------------------------
    # Check we have got a valid connection to the database
    #-----------------------------------------------------
    $this->Manifestation( $db_connection );

    $this->date_entity = new Islamic_Date_Entity( $this->db_connection );
  }

  #----------------------------------------------------------------------------------

  function proj_list_form_sections() {  # overrides parent class

    $form_sections = array( 'basic_details'      =>  'Basic details',
                            'manif_lang'         =>  'Language of manifestation',
                            'incipit_and_excipit'=>  'Incipit, explicit and colophon',
                            'manif_dates'        =>  'Date of copying',
                            'manif_study'        =>  'Teachers, students and where studied',
                            'manif_annotator'    =>  'Annotations',
                            'patrons_of_manif'   =>  'Patron',
                            'dedicatees_of_manif'=>  'Dedicatee',
                            'former_owners'      =>  'Former owners',
                            'endower_of_manif'   =>  'Endowers and endowees',
                            'enclosures'         =>  $this->proj_get_field_label( 'enclosures' ),
                            'enclosed_in'        =>  $this->proj_get_field_label( 'enclosing_section' ),
                            'paper_and_markings' =>  'Paper and markings',
                            'scribe_hand'        =>  $this->proj_get_field_label( 'scribe_hand' ),
                            'place_of_copying'   =>  'Place of copying',
                            'manifestation_notes'=>  'Notes on manifestation',
                            'imgs'               =>  'Images' );
    return $form_sections;
  }
  #-----------------------------------------------------

  function place_of_copying_section() { # overrides parent class

    $this->proj_form_section_links( 'place_of_copying', $heading_level = 'bold' );

    html::new_paragraph();

    $this->place_of_copying_field(); 
    html::new_paragraph();

    $this->place_of_copying_notes_field();
    html::new_paragraph();

    html::div_start( 'class="workfield"' );
    $this->extra_save_button( $prefix = 'place_of_copying' );
    html::div_end();

    html::new_paragraph();
    html::horizontal_rule();
  }
  #-----------------------------------------------------
  function scribes_field() {

    parent::scribes_field();

    $this->copyist_notes_field();
  }
  #-----------------------------------------------------

  function enclosures_field() {  # we use this for contents of 'composite work/codex'

    html::bold_start();
    echo 'If the current document is a codex/composite work, comprises:';
    html::bold_end();
    html::new_paragraph();

    $this->proj_edit_area_calling_popups( $fieldset_name               = 'enclosure_to_this',
                                          $section_heading             = '',
                                          $decode_display              = 'item comprised',
                                          $separate_section            = FALSE,
                                          $extra_notes                 = NULL,
                                          $popup_object_name           = 'popup_manifestation',
                                          $popup_object_class          = 'popup_manifestation' );

    $this->contents_notes_field();
    html::new_paragraph();
  }
  #----------------------------------------------------------------------------------

  function enclosing_field() {
    parent::enclosing_field();

    # If the item is already in a codex/composite work, put up a message saying it could
    # just possibly be in more than one (if this manifestation is in fact a printed edition)
    $codex = $this->rel_obj->get_other_side_for_this_on_left(  # inner (L) is in outer (R)
                             $this_table = $this->proj_manifestation_tablename(), 
                             $this_id = $this->manifestation_id, 
                             $reltype = RELTYPE_INNER_MANIF_ENCLOSED_IN_OUTER_MANIF, 
                             $other_table = $this->proj_manifestation_tablename());

    if( count( $codex ) > 0 ) {
      html::new_paragraph();
      html::italic_start();
      html::span_start( 'class="workfieldaligned"' );
      echo '(The option to add another composite work is made available because multiple copies';
      html::span_end();
      echo LINEBREAK;
      html::span_start( 'class="workfieldaligned"' );
      echo 'of a printed edition could have been bound into different composite works.)';
      html::span_end();
      html::italic_end();
    }

    $this->codex_notes_field();
  }
  #----------------------------------------------------------------------------------

  function manifestation_title_field() {  # Override in child class if manifestation title required.

    html::div_start( 'class="workfield"' );

    html::input_field( 'manifestation_title',  $label = 'Title of manifestation', 
                       $this->manifestation_title, FALSE, FLD_SIZE_WORK_TITLE );
    html::new_paragraph();

    html::textarea( 'manifestation_alternative_titles', $rows = 2, $cols = FLD_SIZE_WORK_TITLE,  
                    $value = $this->manifestation_alternative_titles, $label = 'Alternative titles' );

    html::div_end();  # end div workfield
    html::new_paragraph();
  }
  #----------------------------------------------------------------------------------

  function printed_edition_field() {

    html::div_start( 'class="workfield"' );

    html::input_field( 'printed_edition_details',
                       $this->proj_get_field_label( 'printed_edition_details' ),
                       $this->printed_edition_details, 
                       FALSE, FLD_SIZE_WORK_TITLE );

    html::div_end();

    $this->proj_publication_popups( $calling_field = 'printed_edition_details' );
  }
  #-----------------------------------------------------

  function echo_shelfmark_or_printed_edition() { # Method used in table of manifestations 
                                                 # displayed in 'Manifestations' tab of Work edit.

    $manifestation_title = $this->core[ 'manifestation_title' ];
    $manifestation_alternative_titles = $this->core[ 'manifestation_alternative_titles' ];

    $this->echo_safely( $this->manifestation_id . ': ');
    $this->echo_safely( $manifestation_title );

    if( $manifestation_alternative_titles > '' ) {
      echo LINEBREAK . 'Alternative title(s): ' . LINEBREAK;
      $this->echo_safely_with_linebreaks( $manifestation_alternative_titles );
    }

    echo LINEBREAK;
    parent::echo_shelfmark_or_printed_edition();  # now display shelfmark, printed edition details etc
  }
  #----------------------------------------------------------------------------------

  function manifestation_entry_form() {

    $this->set_manifestation_defaults();

    $this->manif_basic_details_fieldgroup();

    $this->manif_language_fieldgroup();

    $this->manif_incipit_and_excipit_fieldgroup();
  
    $this->manif_date_fieldgroup();

    $this->manif_study_fieldgroup();

    $this->manif_annotator_fieldgroup();

    $this->patrons_of_manif_fieldgroup();

    $this->dedicatees_of_manif_fieldgroup();

    $this->manif_former_owners_fieldgroup();

    $this->endower_of_manif_fieldgroup();

    $this->endowee_of_manif_fieldgroup();

    $this->manif_enclosed_fieldgroup();

    $this->manif_enclosing_fieldgroup();

    $this->manif_paper_and_markings_fieldgroup();

    $this->manif_scribe_fieldgroup();

    $this->place_of_copying_section(); # in this class is just a stub; must be overridden in child class to activate

    $this->manif_notes_fieldgroup();

    $this->manif_image_fieldgroup();
  }
  #----------------------------------------------------------------------------------

  function set_manifestation_defaults() {  # for a new record, copy some details from the parent work

    if( $this->manifestation_id ) return;  # never overwrite existing data with defaults

    $work = new Islamic_Work( $this->db_connection );
    $found = $work->set_work_by_text_id( $this->work_id );
    if( ! $found ) die( 'Invalid work details.' );

    $copyfields = $this->list_columns_for_defaults();

    foreach( $copyfields as $from => $to ) {
      $fromval = $work->$from;
      $toval = $this->$to;  # there shouldn't be a value in the manifestation field, but let's double-check

      if( $fromval && ! $toval ) {
        $this->$to = $fromval;
      }
    }

    # Prepare to add languages
    if( ! $this->language_obj ) $this->language_obj = new Language( $this->db_connection );
    $this->work_langs = $this->language_obj->get_languages_of_text( 'work', $this->work_id );
  }
  #----------------------------------------------------------------------------------

  function list_columns_for_defaults() {  # for a new record, copy some details from the parent work

    $copyfields = array(  'work_title' => 'manifestation_title',
                          'work_alternative_titles' => 'manifestation_alternative_titles',
                          'incipit' => 'manifestation_incipit',
                          'explicit' => 'manifestation_excipit',
                          'ps' => 'manifestation_ps'  # I am going to cheat and use PS column for colophon
                       );

    return $copyfields;
  }
  #----------------------------------------------------------------------------------

  function is_postgres_timestamp( $parm_value, $allow_blank = TRUE ) {

    return $this->date_entity->is_postgres_timestamp( $parm_value, $allow_blank );
  }
  #-----------------------------------------------------

  function new_manifestation_entered() {

    $this->parent_work = new Islamic_Work( $this->db_connection );
    $found = $this->parent_work->set_work_by_text_id( $this->work_id );
    if( ! $found ) die( 'Invalid work details.' );
  
    $this->copyfields = $this->list_columns_for_defaults();

    $new_manifestation_entered = parent::new_manifestation_entered();

    # If there are no manifestations yet, assume they might want to enter a purely default one
    if( ! $new_manifestation_entered && ! $this->parm_found_in_post( 'delete_manifestation' )) {
      $existing_manifs = $this->select_manifestations_of_work( $this->work_id );
      if( count( $existing_manifs ) == 0 ) $new_manifestation_entered = TRUE;
    }

    return $new_manifestation_entered;
  }
  #-----------------------------------------------------

  function is_empty_or_default_value( $column_name, $value ) { # overrides Manifestation method

    $parent_column = NULL;

    foreach( $this->copyfields as $from => $to ) {
      if( $column_name == $to ) {
        $parent_column = $from;
        break;
      }
    }
    
    if( $parent_column ) {

      $parent_value = $this->parent_work->$parent_column;

      if( strval( $parent_value ) == '0' ) $parent_value = NULL;
      if( strval( $value ) == '0' ) $value = NULL;

      if( $this->escape( $value ) == $this->escape( $parent_value )) {
        return TRUE; # this is a default value based on the parent work
      }
      else {
        return FALSE; # not a default value
      }
    }

    return parent::is_empty_or_default_value( $column_name, $value );
  }
  #-----------------------------------------------------

  function language_of_manifestation_field() {

    if( $this->manifestation_id ) { # existing record
      parent::language_of_manifestation_field();
      return;
    }

    # For a new record, set defaults from work

    $possible_langs = $this->proj_get_possible_languages();
    $actual_langs = array();

    if( is_array( $this->work_langs ) && count( $this->work_langs ) > 0 ) {
      $actual_langs = $this->work_langs;
    }

    if( ! $this->language_obj ) $this->language_obj = new Language( $this->db_connection );
    $this->language_obj->language_entry_fields( $possible_langs, $actual_langs );
  }
  #-----------------------------------------------------

  function check_if_languages_entered() {

    $langs_on_form = parent::check_if_languages_entered();
    if( ! $langs_on_form ) return NULL;
    $langs = array();

    if( ! $this->language_obj ) $this->language_obj = new Language( $this->db_connection );
    $work_langs = $this->language_obj->get_languages_of_text( $object_type = 'work', 
                                                              $id_value = $this->work_id );

    foreach( $langs_on_form as $lang_on_form => $note_on_form ) {
      $is_default = FALSE;
      foreach( $work_langs as $row ) {
        extract( $row, EXTR_OVERWRITE );
        if( $language_code == $lang_on_form && $notes == $note_on_form ) {
          $is_default = TRUE;
          break;
        }
      }
      if( ! $is_default ) { 
        $langs[] = $lang_on_form;
        break;
      }
    }

    if( count( $langs ) > 0 )
      return $langs;
    else
      return NULL;
  }
  #-----------------------------------------------------

  function former_owners_field() {

    parent::former_owners_field();

    html::new_paragraph();

    $this->notes_on_former_owners_field();
  }
  #-----------------------------------------------------

  function manif_incipit_and_excipit_fieldgroup() {  # overrides parent version (has notes fields)

    $this->proj_form_section_links( 'incipit_and_excipit', $heading_level = 'bold' );
    html::new_paragraph();

    html::span_start( 'class="workfieldaligned"' );
    html::bold_start();
    echo 'Incipit:';
    html::bold_end();

    html::div_start( 'class="workfield"' );
    $this->manifestation_incipit_field();
    html::div_end();
    $this->incipit_notes_field();

    html::horizontal_rule( 'class="pale"' );
    html::new_paragraph();

    html::span_start( 'class="workfieldaligned"' );
    html::bold_start();
    echo 'Explicit and colophon:';
    html::bold_end();

    html::div_start( 'class="workfield"' );
    $this->manifestation_excipit_field();
    html::div_end();
    $this->excipit_notes_field();

    html::div_start( 'class="workfield"' );
    $this->extra_save_button( 'incipit_and_excipit' );
    html::div_end();

    html::horizontal_rule();
  }
  #-----------------------------------------------------

  function proj_get_field_label( $fieldname = NULL ) {

    switch( $fieldname ) {
      case 'scribe':
      case 'scribe_hand';
        return 'Copyist';

      case 'enclosures':
        return 'Comprises';

      case 'enclosure':
        return 'Comprises';

      case 'enclosed_in':
        return 'Comprised by';

      case 'enclosing_section':
        return 'Codex/composite work in which comprised';

      case 'enclosing_this':
        return 'composite work';

      case 'number_of_pages_of_document':
        return 'Number of folios';

      case 'id_number_or_shelfmark':
        return 'Shelfmark and folio(s)';

      case 'manifestation_excipit':
        return 'Manifestation explicit';

      case 'manif_dates':
        return 'Hijri date of copying';

      default:
        return parent::proj_get_field_label( $fieldname );
    }
  }
  #-----------------------------------------------------

  function search_help_text() {

    html::new_paragraph();

    echo 'Use the Manifestations Summary field to search information relating to all manifestations of a work;'
         . ' this includes the following details, if known, in this order:';

    html::ulist_start();
    html::listitem( 'Date of copying' );
    html::listitem( 'Shelfmark' );
    html::listitem( 'Alternative title(s)' );
    html::listitem( 'Incipit/explicit' );
    html::listitem( 'Codex/composite document comprising the manifestation,'
                    . ' or the manifestations comprised by a codex/composite document');
    html::ulist_end();
    html::new_paragraph();

    echo 'Use % to search for more than one element of the summary;'
         . ' note that search terms must be entered in the above order.';

    html::new_paragraph();
  }
  #-----------------------------------------------------

  function manifestation_notes_field() {

    parent::manifestation_notes_field();

    html::new_paragraph();
    html::span_start( 'class="workfieldaligned"' );
    echo 'This field is meant for information on page layout (landscape vs. horizontal),'
         . ' diagrams, illustrations, and general notes.';
    html::span_end();
    html::new_paragraph();
  }
  #-----------------------------------------------------

  function manifestation_excipit_field() {

    parent::manifestation_excipit_field();

    html::new_paragraph();

    html::textarea( 'manifestation_ps', FLD_SIZE_MANIF_EXCIPIT_ROWS, FLD_SIZE_MANIF_EXCIPIT_COLS, 
                    $value = $this->manifestation_ps, 
                    $label = $this->proj_get_field_label( 'manifestation_ps' ));
  }
  #-----------------------------------------------------

  function manif_paper_and_markings_fieldgroup() {

    $this->proj_form_section_links( 'paper_and_markings', $heading_level = 'bold' );

    html::div_start( 'class="workfield"' );
    $this->paper_size_field();
    html::new_paragraph();

    $this->text_block_size_field();
    html::new_paragraph();

    $this->paper_type_or_watermark_field();
    html::new_paragraph();

    $this->number_of_pages_of_document_field();
    html::new_paragraph();

    $this->script_field();
    html::new_paragraph();

    $this->lines_per_page_field();
    html::new_paragraph();

    $this->seal_field();
    html::new_paragraph();

    $this->bindings_field();
    html::new_paragraph();

    $this->illustrations_field();
    html::new_paragraph();

    $this->illuminations_field();
    html::new_paragraph();

    $this->notes_on_physical_properties_field();
    html::new_paragraph();

    $this->extra_save_button( 'paper_and_markings' );

    html::div_end();  # end div workfield
    html::new_paragraph();
    html::horizontal_rule();
    html::new_paragraph();
  }
  #-----------------------------------------------------

  function seal_field() {

    html::input_field( 'seal', 'Seal(s)', $value = $this->seal, FALSE, $size = FLD_SIZE_SEAL_COLS, $tabindex=1,
                       NULL, NULL, NULL, 0, ' (up to 500 characters)' );
  }
  #-----------------------------------------------------

  function script_field() {

    html::input_field( 'script', 'Script', $value = $this->script, FALSE, $size = FLD_SIZE_SEAL_COLS, $tabindex=1,
                       NULL, NULL, NULL, 0, ' (up to 100 characters)' );
  }
  #-----------------------------------------------------

  function text_block_size_field() {

    html::input_field( 'text_block_size', 'Text block size', $value = $this->text_block_size, 
                       FALSE, $size = FLD_SIZE_SEAL_COLS, $tabindex=1,
                       NULL, NULL, NULL, 0, ' (up to 100 characters)' );
  }
  #-----------------------------------------------------

  function lines_per_page_field() {

    html::input_field( 'lines_per_page', 
                       $this->proj_get_field_label( 'lines_per_page' ), 
                       $this->lines_per_page, 
                       FALSE, FLD_SIZE_NUM_PAGES_DOC, 1, NULL, NULL, 
                       $input_parms = 'onchange="js_check_value_is_numeric( this )"',
                       $input_instance = 0, $trailing_text = ' (whole numbers only)' );
  }
  #-----------------------------------------------------

  function notes_on_physical_properties_field() {

    html::input_field( 'address', $this->db_get_default_column_label( 'address' ), $value = $this->address, 
                       FALSE, $size = FLD_SIZE_SEAL_COLS, $tabindex=1,
                       NULL, NULL, NULL, 0, ' (unlimited characters)' );

    html::div_end();  # end div workfield
    $this->proj_publication_popups( $calling_field = 'address' );
    html::div_start( 'class="workfield"' );
  }
  #-----------------------------------------------------

  function bindings_field() {

    html::input_field( 'bindings', 'Bindings', $value = $this->bindings, FALSE, $size = FLD_SIZE_SEAL_COLS, $tabindex=1,
                       NULL, NULL, NULL, 0, ' (up to 100 characters)' );
  }
  #-----------------------------------------------------

  function illustrations_field() {

    html::input_field( 'illustrations', 'Illustrations', $value = $this->illustrations, 
                       FALSE, $size = FLD_SIZE_SEAL_COLS, $tabindex=1,
                       NULL, NULL, NULL, 0, ' (up to 100 characters)' );
  }
  #-----------------------------------------------------

  function illuminations_field() {

    html::input_field( 'illuminations', 'Illuminations', $value = $this->illuminations, 
                       FALSE, $size = FLD_SIZE_SEAL_COLS, $tabindex=1,
                       NULL, NULL, NULL, 0, ' (up to 100 characters)' );
  }
  #-----------------------------------------------------

  function db_get_default_column_label( $column_name = NULL ) {

    switch( $column_name ) {

      case 'manifestation_id':
        return 'Manifestation ID';

      case 'manifestation_alternative_titles':
        return 'Alternative title(s)';

      case 'manifestation_title':
        return 'Title of manifestation';

      case 'manifestation_type':
        return 'Document type';

      case 'printed_edition_details':
        return 'Bibliographic references';

      case 'manifestation_incipit':
        return 'Incipit';

      case 'manifestation_excipit':
        return 'Explicit';

      case 'manifestation_ps':
        return 'Colophon';

      case 'endorsements':
        return 'Text of annotation(s)';

      case 'address':
        if( $this->in_overview ) return 'Notes on physical properties';
        return 'Notes';

      case 'number_of_pages_of_document':
        return 'No. of folios';

      case 'paper_type_or_watermark':
        return 'Paper type, watermark';

      case 'manifestation_creation_date':
        if( $this->in_overview )
          return 'Hijri date of copying for ordering';
        else
          return 'Hijri date of copying';

      case 'manifestation_creation_date_gregorian':
        if( $this->in_overview )
          return 'CE date of copying for ordering';
        else
          return 'CE date of copying';

      case 'scribe':
        return 'Copyist';

      case 'date_of_work_std':
      case 'christian_date':
      case 'creators_for_display':
      case 'creators_searchable':
      case 'iwork_id':
        if( ! $this->impact_work_obj ) $this->impact_work_obj = new Islamic_Work( $this->db_connection );
        return $this->impact_work_obj->db_get_default_column_label( $column_name );

      case 'id_number_or_shelfmark':
        return 'Shelfmark and folio(s)';

      case 'manif_incipit_explicit_colophon':
        return 'Manifestation incipit, explicit and colophon';

      case 'enclosed_in':
        return 'Codex in which comprised';

      case 'enclosures':
        return 'Comprises';

      case 'relationship_id':
        return 'System ID';

      case 'manifestation_notes':
        return 'General notes on manifestation';

      default:
        return parent::db_get_default_column_label( $column_name );
    }
  }
  #-----------------------------------------------------

  function db_list_columns(  $table_or_view = NULL ) {  # overrides parent class

    $rawcols = parent::db_list_columns( $table_or_view );
    if( ! is_array( $rawcols )) return NULL;

    if( ! $this->impact_work_obj ) $this->impact_work_obj = new Islamic_Work( $this->db_connection );
    $this->impact_work_obj->entering_selection_criteria = $this->entering_selection_criteria;
    $this->impact_work_obj->reading_selection_criteria = $this->reading_selection_criteria;
    $workcols = $this->impact_work_obj->db_list_columns( $this->proj_work_viewname() );

    $columns = array();
    foreach( $rawcols as $row ) {
      extract( $row, EXTR_OVERWRITE );
      $skip_it = FALSE;

      $row[ 'search_help_text' ] = NULL;
      $row[ 'search_help_class' ] = NULL;
      $row[ 'section_heading' ] = NULL;

      #---------------------------------------------
      # Some columns are queryable but not displayed
      #---------------------------------------------
      if( ! $this->entering_selection_criteria && ! $this->reading_selection_criteria ) {
        switch( $column_name ) {
          case 'manifestation_id':
          case 'creators_searchable':
            $skip_it = TRUE;
            break;
        }
      }

      #---------------------------------------------
      # Some columns are displayed but not queryable 
      #---------------------------------------------
      else if( $this->entering_selection_criteria || $this->reading_selection_criteria ) {
        switch( $column_name ) {
          case 'creators_for_display':
            $skip_it = TRUE;
            break;
        }
      }

      #-------------------------------------------------------------------
      # Some columns are in the view so they can be used behind the scenes
      #-------------------------------------------------------------------
      switch( $column_name ) {

        case 'is_composite_document':
        case 'work_id':
          $skip_it = TRUE;
          break;

        default:
          break;
      }
      if( $skip_it ) continue;

      #------------------------------
      # Column labels and search help
      #------------------------------
      switch( $column_name ) {

        case 'creators_searchable':
        case 'creators_for_display':
        case 'date_of_work_std':
        case 'christian_date':
          foreach( $workcols as $wc ) {
            if( $wc[ 'column_name' ] == $column_name ) {
              $row[ 'column_label' ] = $wc[ 'column_label' ];
              $row[ 'search_help_text' ] = $wc[ 'search_help_text' ];
              break;
            }
          }
          break;

        case 'iwork_id':
          $row[ 'search_help_text' ] = 'ID within the IMPAcT database of the original work.';
          break;

        case 'manifestation_id':
          $row[ 'search_help_text' ] = 'ID of a single manifestation of a work'
                                     . " (e.g. 'W123-a' for the first manifestation of work '123').";
          break;

        case 'manifestation_creation_date':
          $row[ 'column_label' ] = $this->db_get_default_column_label( $column_name );
          $row[ 'search_help_text' ] = 'Enter as yyy or yyyy.';
          break;

        case 'manifestation_creation_date_gregorian':
          $row[ 'column_label' ] = $this->db_get_default_column_label( $column_name );
          $row[ 'search_help_text' ] = 'Enter as yyyy.';
          break;

        case 'manif_incipit_explicit_colophon':
          $row[ 'column_label' ] = 'Manifestation incipit, explicit and colophon';
          break;

        case 'repository':
          $row[ 'search_help_class' ] = 'repository';
          break;

        case 'id_number_or_shelfmark':
          $row[ 'column_label' ] = $this->db_get_default_column_label( $column_name );
          break;

        default:
          break;
      }

      if( $column_name == 'iwork_id' )
        $row[ 'section_heading' ] = 'Work details';
      elseif( $column_name == 'manifestation_id' )
        $row[ 'section_heading' ] = 'Manifestation details';
        

      $columns[] = $row;
    }
    return $columns;
  }
  #-----------------------------------------------------

  function get_columns_for_overview() {

    $cols = array( 

      'running_total',
      'manifestation_id',

      'manifestation_title',
      'manifestation_alternative_titles',
      'manifestation_type',
      'repository',                       # relationship to institution
      'id_number_or_shelfmark',
      'printed_edition_details',

      'manifestation_is_translation',
      'language_of_manifestation',

      'manifestation_incipit',
      'bibliographic_references_and_other_notes_on_incipit',

      'manifestation_excipit',
      'manifestation_ps',
      'bibliographic_references_and_other_notes_on_explicit_or_colophon',

      'manifestation_creation_date_as_marked',
      'manifestation_creation_calendar',
      'hijri_date_of_copying',
      'manifestation_creation_date',
      'manifestation_creation_date_gregorian',
      'manifestation_creation_date_inferred',
      'manifestation_creation_date_uncertain',
      'manifestation_creation_date_approx',
      'bibliographic_references_and_other_notes_on_date_of_copying',

      'teachers',                   # relationships to people
      'bibliographic_references_and_other_notes_on_teachers',

      'students',
      'bibliographic_references_and_other_notes_on_students',

      'place_where_studied',        # relationship to place
      'bibliographic_references_and_other_notes_on_places_where_studied',

      'endorsements',               
      'annotators',                 # relationship to people
      'bibliographic_references_and_other_notes_on_annotations',

      'patrons',                    # relationships to people
      'bibliographic_references_and_other_notes_on_patrons',

      'dedicatees',
      'bibliographic_references_and_other_notes_on_dedicatees',

      'former_owners',
      'bibliographic_references_and_other_notes_on_former_owners',

      'endowers',
      'bibliographic_references_and_other_notes_on_endowers',

      'endowees',
      'bibliographic_references_and_other_notes_on_endowees',

      'enclosures',                 # relationships to manifestations
      'bibliographic_references_and_other_notes_on_items_comprised',

      'enclosed_in',
      'bibliographic_references_and_other_notes_on_codex',

      'paper_size',
      'text_block_size',
      'paper_type_or_watermark',
      'number_of_pages_of_document',
      'script',
      'lines_per_page',
      'seal',
      'bindings',
      'illustrations',
      'illuminations',
      'address', # used as notes at their request

      'scribe',                          # relationship to person
      'bibliographic_references_and_other_notes_on_copyist',

      'place_of_copying',
      'bibliographic_references_and_other_notes_on_place_of_copying',

      'manifestation_notes',             # relationship to comments
      'manifestation_images'             # relationship to images
    );
    return $cols;
  }
  #-----------------------------------------------------

  function select_extra_details_for_overview() {

    parent::select_extra_details_for_overview();

    $this->teachers = $this->get_teachers_decoded();
    $this->students = $this->get_students_decoded();
    $this->place_where_studied = $this->get_places_where_studied_decoded();
    $this->annotators = $this->get_annotators_decoded();
    $this->patrons = $this->get_patrons_decoded();
    $this->dedicatees = $this->get_dedicatees_decoded();
    $this->endowers = $this->get_endowers_decoded();
    $this->endowees = $this->get_endowees_decoded();
    $this->place_of_copying = $this->get_place_of_copying_decoded();

    $this->hijri_date_of_copying = $this->date_entity->hijri_date_in_words( 
                                   $this->manifestation_creation_date_year,
                                   $this->manifestation_creation_date_month,
                                   $this->manifestation_creation_date_day,
                                   $this->manifestation_creation_date2_year,
                                   $this->manifestation_creation_date2_month,
                                   $this->manifestation_creation_date2_day,
                                   $this->manifestation_creation_date_is_range );

    # Get all the various 'bibliographic notes' fields.
    $cols = $this->get_columns_for_overview();
    foreach( $cols as $colname ) {
      $reltype = '';
      if( $this->string_starts_with( $colname, 'bibliographic_references_and_other_notes' )) {
        switch( $colname ) {

          case 'bibliographic_references_and_other_notes_on_incipit':
            $reltype = RELTYPE_COMMENT_REFERS_TO_INCIPIT_OF_MANIF;
            break;

          case 'bibliographic_references_and_other_notes_on_explicit_or_colophon':
            $reltype = RELTYPE_COMMENT_REFERS_TO_EXCIPIT_OF_MANIF;
            break;

          case 'bibliographic_references_and_other_notes_on_date_of_copying':
            $reltype = RELTYPE_COMMENT_REFERS_TO_DATE;
            break;

          case 'bibliographic_references_and_other_notes_on_teachers':
            $reltype = RELTYPE_COMMENT_REFERS_TO_TEACHER_OF_MANIF;
            break;

          case 'bibliographic_references_and_other_notes_on_students':
            $reltype = RELTYPE_COMMENT_REFERS_TO_STUDENT_OF_MANIF;
            break;

          case 'bibliographic_references_and_other_notes_on_places_where_studied':
            $reltype = RELTYPE_COMMENT_REFERS_TO_PLACE_STUDIED_OF_MANIF;
            break;

          case 'bibliographic_references_and_other_notes_on_annotations':
            $reltype = RELTYPE_COMMENT_REFERS_TO_ANNOTATOR_OF_MANIF;
            break;

          case 'bibliographic_references_and_other_notes_on_patrons':
            $reltype = RELTYPE_COMMENT_REFERS_TO_PATRONS_OF_MANIF;
            break;

          case 'bibliographic_references_and_other_notes_on_dedicatees':
            $reltype = RELTYPE_COMMENT_REFERS_TO_DEDICATEES_OF_MANIF;
            break;

          case 'bibliographic_references_and_other_notes_on_former_owners':
            $reltype = RELTYPE_COMMENT_REFERS_TO_FORMER_OWNERS_OF_MANIF;
            break;

          case 'bibliographic_references_and_other_notes_on_endowers':
            $reltype = RELTYPE_COMMENT_REFERS_TO_ENDOWERS_OF_MANIF;
            break;

          case 'bibliographic_references_and_other_notes_on_endowees':
            $reltype = RELTYPE_COMMENT_REFERS_TO_ENDOWEES_OF_MANIF;
            break;

          case 'bibliographic_references_and_other_notes_on_items_comprised':
            $reltype = RELTYPE_COMMENT_REFERS_TO_CONTENTS_OF_MANIF;
            break;

          case 'bibliographic_references_and_other_notes_on_codex':
            $reltype = RELTYPE_COMMENT_REFERS_TO_CODEX_OF_MANIF;
            break;

          case 'bibliographic_references_and_other_notes_on_copyist':
            $reltype = RELTYPE_COMMENT_REFERS_TO_COPYIST_OF_MANIF;
            break;

          case 'bibliographic_references_and_other_notes_on_place_of_copying':
            $reltype = RELTYPE_COMMENT_REFERS_TO_PLACE_OF_COPYING;
            break;
        }

        if( $reltype ) {
          $this->$colname = $this->proj_get_decoded_rels( $required_relationship_type = $reltype, 
                                                          $required_table = $this->proj_comment_tablename(), 
                                                          $this_side = 'right' ); # Comment refers to entity.
        }
      }
    }

    $this->in_overview = TRUE;
  }
  #-----------------------------------------------------

  function get_teachers_decoded() {

    $result = $this->proj_get_decoded_rels( $required_relationship_type = RELTYPE_PERSON_TAUGHT_TEXT_OF_MANIF, 
                                            $required_table = $this->proj_person_tablename(), 
                                            $this_side = 'right' ); # Person taught manif. This is manif.
    return $result;
  }
  #-----------------------------------------------------

  function get_students_decoded() {

    $result = $this->proj_get_decoded_rels( $required_relationship_type = RELTYPE_PERSON_STUDIED_TEXT_OF_MANIF, 
                                            $required_table = $this->proj_person_tablename(), 
                                            $this_side = 'right' ); # Person studied manif. This is manif.
    return $result;
  }
  #-----------------------------------------------------

  function get_places_where_studied_decoded() {

    $result = $this->proj_get_decoded_rels( $required_relationship_type = RELTYPE_MANIF_WAS_STUDIED_IN_PLACE, 
                                            $required_table = $this->proj_location_tablename(), 
                                            $this_side = 'left' ); # Manif was studied in place. This is manif.
    return $result;
  }
  #-----------------------------------------------------

  function get_annotators_decoded() {

    $result = $this->proj_get_decoded_rels( $required_relationship_type = RELTYPE_PERSON_ANNOTATED_MANIF, 
                                            $required_table = $this->proj_person_tablename(), 
                                            $this_side = 'right' ); # Person annotated manif. This is manif.
    return $result;
  }
  #-----------------------------------------------------

  function get_patrons_decoded() {

    $patron = $this->proj_get_decoded_rels( $required_relationship_type = RELTYPE_PERSON_WAS_PATRON_OF_MANIF, 
                                            $required_table = $this->proj_person_tablename(), 
                                            $this_side = 'right' ); # Person was patron of manif. This is manif.

    $requester = $this->proj_get_decoded_rels( $required_relationship_type = RELTYPE_PERSON_ASKED_FOR_COPYING_OF_MANIF, 
                                               $required_table = $this->proj_person_tablename(), 
                                               $this_side = 'right' ); # Person asked for copying of manif. This is manif.

    if( $requester ) $requester = 'Person who asked for manuscript to be copied: ' . $requester;

    $result = '';

    if( $patron && $requester )
      $result = '<ul><li>Patron: ' . $patron . '</li><li>' . $requester . '</li></ul>';
    elseif( $patron )
      $result = $patron;
    elseif( $requester )
      $result = $requester;

    return $result;
  }
  #-----------------------------------------------------

  function get_dedicatees_decoded() {

    $result = $this->proj_get_decoded_rels( $required_relationship_type = RELTYPE_PERSON_WAS_DEDICATEE_OF_MANIF, 
                                            $required_table = $this->proj_person_tablename(), 
                                            $this_side = 'right' ); # Person was dedicatee of manif. This is manif.
    return $result;
  }
  #-----------------------------------------------------

  function get_endowers_decoded() {

    $result = $this->proj_get_decoded_rels( $required_relationship_type = RELTYPE_PERSON_WAS_ENDOWER_OF_MANIF, 
                                            $required_table = $this->proj_person_tablename(), 
                                            $this_side = 'right' ); # Person was endower of manif. This is manif.
    return $result;
  }
  #-----------------------------------------------------

  function get_endowees_decoded() {

    $result = $this->proj_get_decoded_rels( $required_relationship_type = RELTYPE_PERSON_WAS_ENDOWEE_OF_MANIF, 
                                            $required_table = $this->proj_person_tablename(), 
                                            $this_side = 'right' ); # Person was endowee of manif. This is manif.
    return $result;
  }
  #-----------------------------------------------------

  function get_place_of_copying_decoded() {

    $result = $this->proj_get_decoded_rels( $required_relationship_type = RELTYPE_MANIF_WAS_COPIED_AT_PLACE, 
                                            $required_table = $this->proj_location_tablename(), 
                                            $this_side = 'left' ); # Manif was copied at place. This is manif.
    return $result;
  }
  #-----------------------------------------------------
}
?>
