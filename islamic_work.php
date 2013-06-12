<?php
/*
 * IMPAcT-specific version of Work class
 * Subversion repository: https://damssupport.bodleian.ox.ac.uk/svn/impact/php
 * Author: Sushila Burgess
 *
 */

define( 'FLD_SIZE_WORK_TITLE', 80 );
define( 'WORK_ALTERNATIVE_TITLES_ROWS', 3 );

class Islamic_Work extends Editable_Work {

  #----------------------------------------------------------------------------------

  function Islamic_Work( &$db_connection ) {

    #-----------------------------------------------------
    # Check we have got a valid connection to the database
    #-----------------------------------------------------
    $this->Editable_Work( $db_connection );

    $this->date_entity = new Islamic_Date_Entity( $this->db_connection );
    $this->manifest_obj = new Islamic_Manifestation( $this->db_connection );
  }

  #----------------------------------------------------------------------------------

  function proj_list_form_sections() {

    $form_sections = array();

    $selected_tab = $this->read_post_parm( 'selected_tab' );
    if( ! $selected_tab ) $selected_tab = DEFAULT_WORK_EDIT_TAB;

    switch( $selected_tab ) {
      case 'work_tab':
        $form_sections = array( 'authors'         => 'Author',
                                'title_of_work'   => 'Title',
                                'type_of_work'    => 'Type and subject of work',
                                'lang_of_work'    => 'Language',
                                'patrons'         => 'Patrons',
                                'dedicatees'      => 'Dedicatees',
                                'earlier_works'   => 'Basis text commented on, continued, summarised, translated etc',
                                'later_works'     => 'Known commentaries, continuations, summaries or translations etc',
                                'addressees'      => 'Addressees' );
        break;

      case 'other_tab':
        $parent_form_sections = parent::proj_list_form_sections();
        $form_sections = array();
        foreach( $parent_form_sections as $code => $label ) {
          if( $code == 'quotes_from_work' ) {
            $form_sections[ 'quotes_from_work' ] = 'Incipit';
            $form_sections[ 'explicit' ] = 'Explicit';
            $form_sections[ 'colophon' ] = 'Colophon';
          }
          elseif( $code == 'abstract_and_keywords' ) {  # EMLO has 'Abstract, subjects and keywords' here
            $form_sections[ "$code" ] = 'Abstract and keywords';
          }
          elseif( $code == 'people_mentioned' ) {
            $form_sections[ "$code" ] = 'Associated people';
          }
          elseif( $code == 'places_mentioned' ) {
            $form_sections[ "$code" ] = 'Associated places';
          }
          elseif( $code == 'works_mentioned' ) {
            $form_sections[ "$code" ] = 'Associated primary sources';
          }
          elseif( $code == 'general_notes' ) {
            $form_sections[ "$code" ] = 'General notes on primary source';
          }
          else
            $form_sections[ "$code" ] = $label;
        }
        break;
        
      default:
        return parent::proj_list_form_sections();
    }

    return $form_sections;
  }
  #-----------------------------------------------------

  function list_tabs( $get_all_possible = FALSE ) {

    $tabs = array( 'work_tab'           => 'Author, title and subject',
                   'dates_tab'          => 'Dates',
                   'places_tab'         => 'Places',
                   'other_tab'          => 'Content',
                   'manifestations_tab' => 'Manifestations',
                   'related_tab'        => 'Related resources',
                   'overview_tab'       => 'Overview' );

    if( ! $get_all_possible ) {
      if( ! $this->iwork_id ) { # new record, so only display the most basic tabs
        $tabs_for_new_record = 3; # title and author, dates, places
        while( count(  $tabs ) > $tabs_for_new_record ) {
          $removed = array_pop( $tabs );
        }
      }
    }

    return $tabs;
  }
  #-----------------------------------------------------

  function set_fields_and_functions( $tab ) {

    $fields = array();
    $funcs = array();

    $fields[] = 'work_to_be_deleted';
    $fields[] = 'edit_status';

    switch( $tab ) {

      case 'work_tab':
        $fields[] = 'work_type';
        $fields[] = 'work_title';
        $fields[] = 'work_alternative_titles';
        $fields[] = 'title_of_work_inferred';
        $fields[] = 'title_of_work_uncertain';
        $fields[] = 'title_of_work_unknown';
        $fields[] = 'authors_as_marked';
        $fields[] = 'addressees_as_marked';
        $fields[] = 'authors_inferred';
        $fields[] = 'authors_uncertain';
        $fields[] = 'addressees_inferred';
        $fields[] = 'addressees_uncertain';

        $funcs[] = 'save_author';
        $funcs[] = 'save_patrons_of_work';
        $funcs[] = 'save_dedicatees_of_work';
        $funcs[] = 'save_works_discussed';
        $funcs[] = 'save_works_discussing';
        $funcs[] = 'save_addressee';
        $funcs[] = 'save_subjects';
        $funcs[] = 'save_languages';

        break;

      default:
        parent::set_fields_and_functions( $tab );
        return;
    }

    $this->fields_on_current_tab = $fields;
    $this->save_functions_for_current_tab = $funcs;
  }
  #-----------------------------------------------------

  function work_tab() {

    #-------
    # Author
    #-------
    $this->author_field( $horizontal_rule = FALSE );
    $this->extra_save_button( 'authors' );
    html::horizontal_rule();

    #--------------
    # Title of work
    #--------------
    $this->proj_form_section_links( 'title_of_work', $heading_level = 4 );
    $this->work_title_field();
    $this->work_alternative_titles_field();
    $this->title_flags();

    $this->proj_notes_field( RELTYPE_COMMENT_REFERS_TO_TITLE_OF_WORK, 
                            'Bibliographic references and other notes on title of work:' );
    html::new_paragraph();

    $this->extra_save_button( 'title_of_work' );
    html::horizontal_rule();

    #-------------------------
    # Type and subject of work
    #-------------------------
    $this->proj_form_section_links( 'type_of_work', $heading_level = 4 );
    $this->work_type_field();
    $this->work_subjects_field();

    $this->proj_notes_field( RELTYPE_COMMENT_REFERS_TO_TYPE_OF_WORK, 
                            'Bibliographic references and other notes on type or subject of work:' );
    html::new_paragraph();
    $this->extra_save_button( 'type_of_work' );

    #---------
    # Language
    #---------
    html::horizontal_rule();
    $this->proj_form_section_links( 'lang_of_work', $heading_level = 4 );
    $this->languages_field();

    #--------
    # Patrons
    #--------
    $this->patrons_field();
    $this->extra_save_button( 'patrons' );

    #-----------
    # Dedicatees
    #-----------
    $this->dedicatees_field();
    $this->extra_save_button( 'dedicatees' );

    #--------------
    # Related works
    #--------------
    $this->earlier_work_discussed_by_this_field();

    $this->later_work_discussing_this_field();

    #-----------
    # Addressees
    #-----------
    $this->addressee_field();
    $this->extra_save_button( 'addressees' );
  }
  #-----------------------------------------------------

  function work_title_field() {

    html::span_start( 'class="workfield"' );

    html::input_field( 'work_title',  $label = 'Title of work', $this->work_title, FALSE, FLD_SIZE_WORK_TITLE );
    
    html::span_end( 'workfield' );
    html::new_paragraph();
  }
  #-----------------------------------------------------

  function work_alternative_titles_field() {

    html::span_start( 'class="workfield"' );

    $this->proj_textarea( 'work_alternative_titles', WORK_ALTERNATIVE_TITLES_ROWS, FLD_SIZE_WORK_TITLE,
                          $value = $this->work_alternative_titles, $label = 'Alternative titles' );
    
    html::span_end( 'workfield' );

    echo LINEBREAK;
    html::span_start( 'class="workfieldaligned"' );
    html::italic_start();
    echo 'Please put each alternative title on a separate line.';
    html::italic_end();
    html::span_end( 'workfieldaligned italic' );
  }
  #-----------------------------------------------------

  function title_flags() {

    html::new_paragraph();

    html::span_start( 'class="workfield"' );
    html::label( 'Issues with title of work: ' );
    html::span_end( 'workfield' );

    html::ulist_start( 'class="dateflags"' );

    html::listitem_start();
    $this->flag_inferred_title_field() ;
    html::listitem_end();

    html::listitem_start();
    $this->flag_uncertain_title_field() ;
    html::listitem_end();

    html::listitem_start();
    $this->flag_unknown_title_field() ;
    html::listitem_end();

    html::ulist_end();
  }
  #-----------------------------------------------------

  function flag_inferred_title_field() {

    $this->basic_checkbox( 'title_of_work_inferred', 'Title is inferred', $this->title_of_work_inferred );
  }
  #-----------------------------------------------------

  function flag_uncertain_title_field() {

    $this->basic_checkbox( 'title_of_work_uncertain', 'Title is uncertain', $this->title_of_work_uncertain );
  }
  #-----------------------------------------------------

  function flag_unknown_title_field() {

    $this->basic_checkbox( 'title_of_work_unknown', 'Title is unknown', $this->title_of_work_unknown );
  }
  #-----------------------------------------------------

  function work_type_field() {

    if( ! $this->work_type_obj ) $this->work_type_obj = new Work_Type( $this->db_connection );

    html::span_start( 'class="workfield"' );

    $this->work_type_obj->lookup_table_dropdown( $field_name = 'work_type', $field_label = 'Type of work', 
                                                  $selected_id = $this->work_type );

    html::span_end( 'workfield' );
    html::new_paragraph();
  }
  #-----------------------------------------------------

  function work_subjects_field() {

    $subj_obj = new Subject( $this->db_connection );
    $subj_obj->subject_entry_fields( $this->work_id );
  }
  #-----------------------------------------------------

  function incipit_field() {

    parent::incipit_field();
    html::div_end( 'workfield' );

    $this->proj_notes_field( RELTYPE_COMMENT_REFERS_TO_INCIPIT_OF_WORK, 
                            'Bibliographic references and other notes on incipit of work:' );

    $this->extra_save_button( 'quotes_from_work' );
    html::div_start( 'class="workfield"' );
    html::horizontal_rule();
  }
  #-----------------------------------------------------

  function excipit_field() {

    $this->proj_form_section_links( 'explicit', $heading_level = 4 );

    parent::excipit_field();
    html::div_end( 'workfield' );

    $this->proj_notes_field( RELTYPE_COMMENT_REFERS_TO_EXCIPIT_OF_WORK, 
                            'Bibliographic references and other notes on explicit of work:' );

    $this->extra_save_button( 'explicit' );
    html::div_start( 'class="workfield"' );
    html::horizontal_rule();
  }
  #-----------------------------------------------------

  function ps_field() {  # am actually using this field for Colophon in the case of IMPAcT

    $this->proj_form_section_links( 'colophon', $heading_level = 4 );

    parent::ps_field();
    html::div_end( 'workfield' );

    $this->proj_notes_field( RELTYPE_COMMENT_REFERS_TO_COLOPHON_OF_WORK, 
                            'Bibliographic references and other notes on colophon:' );
    html::new_paragraph();
    html::div_start( 'class="workfield"' );
  }
  #-----------------------------------------------------
  #================== Author methods ===================
  #-----------------------------------------------------

  function author_field( $horizontal_rule = TRUE, $heading = 'Authors etc:' ) { 

    html::new_paragraph();
    if( $horizontal_rule ) {
      html::horizontal_rule();
    }

    # Heading is now set in method 'proj_list_form_sections()'.
    $this->proj_form_section_links( 'authors', $heading_level = 4 );

    $this->person_entry_field( $fieldset_name               = FIELDSET_AUTHOR,
                               $section_heading             = '',
                               $decode_display              = 'author',
                               $separate_section            = FALSE );

    echo LINEBREAK;
    $label_part = 'Author';

    $this->authors_as_marked_field( $label_part );

    html::new_paragraph();
    $this->author_flags( $label_part );

    html::new_paragraph();
    $this->notes_on_authors_field( $label_part );
  }
  #-----------------------------------------------------

  function save_author() {

    $this->rel_obj->save_rels_for_field_type( $field_type = FIELDSET_AUTHOR, 
                                              $known_id_value = $this->work_id );
  }
  #=============== End of author methods ===============
  #-----------------------------------------------------

  #============ Methods for works discussed ============

  function earlier_work_discussed_by_this_field() {

    html::horizontal_rule();

    # Heading is now set in method 'proj_list_form_sections()'.
    $this->proj_form_section_links( 'earlier_works', $heading_level = 4 );

    $this->work_entry_field( FIELDSET_WORK_DISCUSSED,
                             $section_heading             = 'Basis texts etc:',
                             $decode_display              = 'basis text',
                             $separate_section            = FALSE );

    $this->proj_notes_field( RELTYPE_COMMENT_REFERS_TO_BASIS_TEXTS_OF_WORK, 
                             'Bibliographical references and any other notes on basis text:' );

    $this->extra_save_button( 'earlier_works' );
  }
  #-----------------------------------------------------

  function save_works_discussed() {

    $this->rel_obj->save_rels_for_field_type( FIELDSET_WORK_DISCUSSED, 
                                              $known_id_value = $this->work_id );
  }
  #========= End of methods for works discussed ========
  #-----------------------------------------------------

  #============ Methods for works discussing this one ============

  function later_work_discussing_this_field() {

    html::horizontal_rule();

    # Heading is now set in method 'proj_list_form_sections()'.
    $this->proj_form_section_links( 'later_works', $heading_level = 4 );

    $this->work_entry_field( FIELDSET_WORK_DISCUSSING,
                             $section_heading             = 'Commentaries etc:',
                             $decode_display              = 'commentary etc',
                             $separate_section            = FALSE );

    $this->proj_notes_field( RELTYPE_COMMENT_REFERS_TO_COMMENTARIES_ON_WORK, 
                             'Bibliographical references and any other notes on commentaries etc:' );

    $this->extra_save_button( 'later_works' );
  }
  #-----------------------------------------------------

  function save_works_discussing() {

    $this->rel_obj->save_rels_for_field_type( FIELDSET_WORK_DISCUSSING, 
                                              $known_id_value = $this->work_id );
  }
  #========= End of methods for works discussing ========
  #-----------------------------------------------------

  function save_subjects() {

    $subj_obj = new Subject( $this->db_connection );
    $subj_obj->save_subjects( $this->work_id );
  }
  #-----------------------------------------------------

  function edit_status_field() { # overrides method from Editable Work which currently just has a stub

    echo 'Status of record: ';
    html::span_start( 'class="highlight1"' );

    html::radio_button( $fieldname = 'edit_status', 
                        $label = 'New', 
                        $value_when_checked = '', 
                        $current_value = $this->edit_status, 
                        $tabindex=1, 
                        $button_instance=1, 
                        $script=NULL );
    echo ' ';

    html::radio_button( $fieldname = 'edit_status', 
                        $label = 'Editing complete', 
                        $value_when_checked = 'ok', 
                        $current_value = $this->edit_status, 
                        $tabindex=1, 
                        $button_instance=2, 
                        $script=NULL );
    echo ' ';

    html::radio_button( $fieldname = 'edit_status', 
                        $label = 'Check with original manuscript', 
                        $value_when_checked = 'chk', 
                        $current_value = $this->edit_status, 
                        $tabindex=1, 
                        $button_instance=3, 
                        $script=NULL );
    html::span_end();
    html::new_paragraph();
  }
  #----------------------------------------------------------------------------------

  function people_mentioned_field() {

    $this->person_entry_field( FIELDSET_PEOPLE_MENTIONED,
                               $section_heading             = NULL, # heading is done by form sections
                               $decode_display              = 'associated person',
                               $separate_section            = FALSE );
  }
  #-----------------------------------------------------

  function notes_on_people_mentioned_field() {

    $this->proj_notes_field( RELTYPE_COMMENT_REFERS_TO_PEOPLE_MENTIONED_IN_WORK,
                             'Bibliographic references and any other notes on people associated with primary source:' );
  }
  #-----------------------------------------------------

  function places_mentioned_field() {

    $this->multiple_place_entry_field( $fieldset_name = FIELDSET_PLACES_MENTIONED,
                                       $section_heading = NULL, # heading is done by form sections
                                       $decode_display = 'associated place',
                                       $separate_section = FALSE, # don't add horizontal rule, bold heading and Save key
                                       $extra_notes = NULL );

    $this->proj_notes_field( RELTYPE_COMMENT_REFERS_TO_PLACES_MENTIONED_IN_WORK,
                             'Bibiliographic references and any other notes on places associated with primary source:' );
  }
  #----------------------------------------------------------------------------------

  function works_mentioned_field() {


    $this->work_entry_field( $fieldset_name = FIELDSET_WORKS_MENTIONED,
                             $section_heading = NULL, # heading is done by form sections
                             $decode_display = 'associated primary source',
                             $separate_section = FALSE, # don't add horizontal rule, bold heading and Save key
                             $extra_notes = NULL );

    $this->proj_notes_field( RELTYPE_COMMENT_REFERS_TO_WORKS_MENTIONED_IN_WORK,
                             'Bibiliographic references and any other notes on associated primary sources:' );
  }
  #----------------------------------------------------------------------------------

  function is_postgres_timestamp( $parm_value, $allow_blank = TRUE ) {

    return $this->date_entity->is_postgres_timestamp( $parm_value, $allow_blank );
  }
  #-----------------------------------------------------
  function yyyy_to_dd_mm_yyyy( $parm_name, $parm_value, $write_to_post = TRUE ) {

    if( strlen( $parm_value ) == strlen( 'yyy' )) # perhaps a 3-figure year from Islamic calendar
      $parm_value = '0' . $parm_value;
  
    return parent::yyyy_to_dd_mm_yyyy( $parm_name, $parm_value, $write_to_post );
  }
  #-----------------------------------------------------

  function date_as_marked_std_field() {

    $this->date_entry_fieldset( $fields = array( 'date_of_work_std', 
                                                 'date_of_work2_std' ),

                                $legend     = 'Hijri date of work', 

                                $extra_msg = 'Leave any part or parts of the date blank if unknown.' 
                                           . '<p>'
                                           . ' For the sake of simplicity, all dates above are based'
                                           . ' on the Islamic lunar calendar.', 

                                $calendar_fieldname = 'original_calendar' );
  }
  #-----------------------------------------------------

  function db_list_columns(  $table_or_view = NULL ) {  # overrides column labels etc from parent class

    $rawcols = parent::db_list_columns( $table_or_view );
    if( ! is_array( $rawcols )) return NULL;

    $columns = array();
    foreach( $rawcols as $row ) {
      $search_help_text = NULL;
      $search_help_class = NULL;
      $section_heading = NULL;
      extract( $row, EXTR_OVERWRITE );

      $skip_it = FALSE;

      #-------------------------------------------------------
      # Exclude some columns from 'order by' dropdown list
      #-------------------------------------------------------
      if( $this->getting_order_by_cols ) {  
        switch( $column_name ) {

          case 'people_and_places_associated_with_manifestations':
          case 'description':
          case 'related_works':
          case 'people_mentioned':
          case 'flags':
          case 'manifestations_searchable':
          case 'manifestations_for_display':
            $skip_it = TRUE;
            break;

          default:
            break;
        }
      }

      if( $skip_it ) continue;
      
      #---------------------------------------------
      # Some columns are queryable but not displayed
      #---------------------------------------------
      if( ! $this->entering_selection_criteria && ! $this->reading_selection_criteria ) {
        switch( $column_name ) {

          case 'people_and_places_associated_with_manifestations':
            $skip_it = TRUE;

          default:
            break;
        }
      }

      if( $skip_it ) continue;

      #------------------
      # Add column labels
      #------------------
      switch( $column_name ) {

        case 'date_of_work_std':
        case 'christian_date':
        case 'ps':
          $column_label = $this->db_get_default_column_label( $column_name );
          break;

        case 'description':
        case 'general_notes':
          $row[ 'searchable' ] = FALSE;
          break;

        case 'creators_for_display':
        case 'creators_searchable':
          $column_label = 'Author';
          break;

        case 'places_from_for_display':
        case 'places_from_searchable':
          $column_label = 'Place of composition';
          break;

        case 'notes_on_authors':
          $column_label = 'Notes on author';
          break;

        case 'flags':
          $column_label = 'Flags';
          $search_help_text = "'Date of work', 'author', 'title of work', 'place of composition',"
                            . " followed by 'inferred', 'uncertain', 'unknown' or 'approximate'; "
                            . " e.g. 'date of work approximate', 'author inferred', 'title of work unknown'.";
          $section_heading = 'System info';
          break;

        case 'manifestations_searchable':
          $column_label = 'Summary of manifestation details';
          break;

      }
      $row[ 'column_label' ] = $column_label;


      #----------------
      # Set search help
      #----------------
      switch( $column_name ) {

        case 'iwork_id':
          $search_help_text = 'The unique ID for the record within the IMPAcT database.';
          $section_heading = 'Core details';
          break;

        case 'work_title':
          $search_help_text = '';
          break;

        case 'description':
          $search_help_text = "The 'Description' field contains the primary title of the work, the author,"
                            . ' type and subject of work, and year of composition, where known.';
          $search_help_class = 'islamic_work';
          break;

        case 'type_of_work':
          $search_help_class = 'work_type';
          break;

        case 'subjects':
          $search_help_class = 'subject';
          $search_help_text = 'Use % to search for works with more than one subject; e.g. <strong>Alchemy%Divination'
                            . '</strong>; switch order of terms if no results are returned.' . LINEBREAK;
          break;

        case 'language_of_work':
          $search_help_class = 'language';
          $search_help_text = 'Use % to search for works in more than one language, e.g. <strong>Arabic%Persian'
                            . '</strong>; switch order of terms if no results are returned.' . LINEBREAK;
          break;

        case 'date_of_work_as_marked':
          $search_help_text = "The date of the work in the author's own words.";
          break;

        case 'date_of_work_std':
          $search_help_text = 'To specify a date range, '
                            . "enter dates 'from' and 'to' as year (yyy or yyyy); "
                            . ' either end of the date-range may be left blank, e.g.<ul>'
                            . "<li>'From <strong>754</strong>' to find works dated from the start of 754 onwards</li>" 
                            . "<li>'To <strong>1505</strong>' to find works dated up to the end of 1505</li></ul>";
          $search_help_text .= 'When ordering results by date of work, note that more specific dates precede'
                            .  ' less specific dates, e.g. <strong>07/835</strong> precedes <strong>835</strong> alone';
          break;

        case 'christian_date':
          $search_help_text = "Enter CE dates as 4-figure years (yyyy).";
          break;


        case 'places_from_searchable':
          $search_help_text = "Use % to search for more than one element of place name, e.g. <strong>Mas'udiyya%Yazd"
                            . '</strong>; switch order of terms if no results are returned.';
          break;

        case 'origin_as_marked':
          $search_help_text = 'The place of composition of a work, as marked within the original manuscript.';
          break;

        case 'creators_searchable':
          $search_help_text = 'Name of author.  Use % to search for more than one author, e.g. <strong>Katibi%Dawani'
                            . '</strong>; switch order of terms if no results are returned.';
          break;

        case 'manif_count':
          $section_heading = 'Manifestations';
          $search_help_text = 'Exact numbers only; to find primary sources with more than x number of manifestations,'
                            . " click 'Advanced Search' above, choose 'greater than' from the dropdown menu and enter x.";
          break;

        case 'related_works':
          $search_help_text = 'Use % to search for more than one primary source type, e.g.'
                            . " <strong>Commentary%Versification</strong>; switch order of terms if no results"
                            . ' are returned.';
          break;
     

        case 'related_resources':
          $search_help_text = 'Links to external online resources such as WorldCat or a library catalogue.';
          break;
     
        case 'people_mentioned':
          $section_heading = 'Associated people, places, primary sources & resources (works & manifestations)';
          $search_help_text = 'Associated people include patron, dedicatee, addressee; associated places include any'
                            . " element of a location name (e.g. 'Rab'-i Rashidi', 'Tabriz')."
                            . ' Use % to search more than one term, e.g. <strong>Ulugh%Bukhara</strong>;'
                            . ' switch order of terms if no results are returned.';
          break;

        case 'incipit_and_explicit':
          $search_help_text = 'Up to 200 characters each.';
          break;

        case 'people_and_places_associated_with_manifestations':
          $search_help_text = 'Associated people include teacher, student, annotator, patron, dedicatee, former owner,'
                            . ' endower, endowee, copyist; associated places include any element of a location name'
                            . " (e.g. 'Mas'udiyya','Yazd').  Use % to search more than one term, e.g."
                            . ' <strong>Nasir%Sumaysatiyya</strong>; switch order of terms if no results are returned.';
          break;

        case 'accession_code':
          $search_help_text = 'Name of the researcher who contributed the data.';
          break;

        case 'change_timestamp':
          $search_help_text = 'Enter as dd/mm/yyyy hh:mm; note that dd/mm/yyyy counts as the start of a day.';
          break;

        case 'edit_status':
          $search_help_text = "To search status of records, type either 'editing complete'"
                            . " or 'check with original manuscript'.";
          break;

        case 'work_to_be_deleted':
          $search_help_text = "Type 'yes' to see records marked for deletion.";
          break;

        case 'images':
          $search_help_text = "Click 'Advanced Search' above and choose 'is not blank' from the dropdown list"
                            . ' to see all records with link to image location. Or enter a known image filename to search'
                            . ' for that particular image.';
          break;

        default:
          break;
      }
      $row[ 'search_help_text' ] = $search_help_text;
      $row[ 'search_help_class' ] = $search_help_class;
      $row[ 'section_heading' ] = $section_heading;

      $columns[] = $row;
    }

    return $columns;
  }
  #-----------------------------------------------------

  function db_get_default_column_label( $column_name = NULL ) {

    switch( $column_name ) {

      case 'date_of_work_std':
        if( $this->in_overview )
          return 'Hijri date of work for ordering';
        else
          return 'Hijri date of work';

      case 'christian_date':
      case 'date_of_work_std_gregorian':
        if( $this->in_overview )
          return 'CE date of work for ordering';
        else
          return 'CE date of work';

      case 'places_from_for_display':
      case 'places_from_searchable':
      case 'origin':
        return 'Place of composition';

      case 'origin_as_marked':
        return 'Place of composition as marked';

      case 'origin_inferred':
        return 'Place of composition inferred';

      case 'origin_uncertain':
        return 'Place of composition uncertain';

      case 'ps':  # we are using 'P.S.' field for colophon
        return 'Colophon';

      case 'work_title':
        return 'Title';

      case 'related_works':
        return 'Associated primary sources';

      case 'people_mentioned':
        return 'People and places associated with work';

      case 'date_of_work_std':
        return 'Date of work in original calendar';

      case 'incipit_and_explicit':
        return 'Incipit / Explicit';

      case 'explicit':
        return 'Explicit';  # override parent Work class which uses 'excipit'

      case 'creators_searchable':
      case 'creators_for_display':
        return 'Author';

      case 'authors_as_marked':
        return 'Author as marked';

      case 'authors_inferred':
        return 'Author inferred';

      case 'authors_uncertain':
        return 'Author uncertain';

      case 'work_alternative_titles':
        return 'Alternative title(s)';

      case 'places_to_for_display':
      case 'places_to_searchable':
        return 'Destination';

      case 'related_resources':
        if( $this->in_overview ) return NULL;  # otherwise the title 'Related resources' appears twice
        return parent::db_get_default_column_label( $column_name );

      default:
        return parent::db_get_default_column_label( $column_name );
    }
  }
  #-----------------------------------------------------

  function db_explain_how_to_query() {

    echo 'Enter selection in one or more fields and click the Search button or press the Return key.'
         . ' Please note:';
    html::new_paragraph();

    html::ulist_start();

    html::listitem( 'Use Arabic script for greater accuracy in searches.' );

    html::listitem_start();
    echo 'When using Latin script omit all diacritics;'
         . " 'ayn and hamza are both represented by a straight apostrophe (e.g. ";
    html::bold_start();
    echo "'Ali";
    html::bold_end();
    echo ' and ';
    html::bold_start();
    echo "masa'il";
    html::bold_end();
    echo ').';
    html::listitem_end();

    html::listitem_start();
    echo 'The case of text fields need not match, e.g. ';
    html::bold_start();
    echo 'rashid';
    html::bold_end();
    echo ' is equivalent to ';
    html::bold_start();
    echo 'Rashid';
    html::bold_end();
    echo '; partial word searches are possible.';
    html::listitem_end();

    html::listitem_start();
    echo 'Use the wildcard ';
    html::bold_start();
    echo '%';
    html::bold_end();
    echo ' (percent sign) to search more than one term in a single field;'
         . ' try switching the order of terms if no results are returned.';
    html::listitem_end();

    html::listitem( 'Fields marked with an asterisk are non-text fields (dates or numbers).');
    html::ulist_end();

    html::new_paragraph();
  }
  #-----------------------------------------------------

  function save_work_fields( $new_record ) {

    parent::save_work_fields( $new_record );  # sets work properties at same time
    if( ! $new_record ) return;

    # See if the new work was a composite type.
    $work_type_obj = new Work_Type( $this->db_connection );
    $is_composite = $work_type_obj->is_composite_type( $this->work_type );
    if( ! $is_composite ) return;

    # If the work is a composite type, create composite manifestation.
    $doc_obj = new Document_Type( $this->db_connection );
    $doctype_code = $doc_obj->get_composite_document_type();
    $this->write_post_parm( 'manifestation_type', $doctype_code );

    $copyfields = $this->manifest_obj->list_columns_for_defaults();
    foreach( $copyfields as $from => $to ) {
      $fromval = $this->$from;
      $this->write_post_parm( $to, $fromval );
    }

    $statement = $this->manifest_obj->get_manifestation_insert_statement();
    $this->db_run_query( $statement );

    $this->rel_obj->insert_relationship( $left_table_name = $this->proj_manifestation_tablename(),
                                         $left_id_value = $this->manifest_obj->manifestation_id,
                                         $relationship_type = RELTYPE_MANIFESTATION_IS_OF_WORK,
                                         $right_table_name = $this->proj_work_tablename(),
                                         $right_id_value = $this->work_id );
  }
  #-----------------------------------------------------
  # Provide search help for the Work Description field in the Search Works screen.
  # We want dropdown lists for both work type and subject.

  function desc_dropdown( $form_name, $field_name = NULL, $copy_field = NULL, $field_label = NULL,
                          $in_table=FALSE, $override_blank_row_descrip = NULL ) {

    $work_type_obj = new Work_Type( $this->db_connection ); 

    $subj_obj = new Lookup_Table( $this->db_connection, 
                                  $lookup_table_name = $this->proj_subject_tablename(), 
                                  $id_column_name    = 'subject_id', 
                                  $desc_column_name  = 'subject_desc' ); 

    html::new_paragraph();

    $work_type_obj->desc_dropdown( $form_name, $field_name . '_work_type', $copy_field, $field_label = 'Type of work',
                                   $in_table, $override_blank_row_descrip );
    html::new_paragraph();

    $subj_obj->desc_dropdown( $form_name, $field_name . '_subject', $copy_field, $field_label = 'Subject',
                              $in_table, $override_blank_row_descrip );

    html::new_paragraph();

    echo 'You can search on all these elements of the work'
        . ' description at once if you wish, but please remember, the details appear in this order: ';

    html::bold_start();
    echo '(1) title; (2) author; (3) type of work; (4) subject; (5) year.';
    html::bold_end();

    echo ' If entering multiple search terms, you need to enter your search terms in that same order,'
         . ' and also, you need to separate them using the wildcard % (percent-sign). ';

    html::new_paragraph();
    echo 'E.g. to find all commentaries on the subject of astronomy, you would need to enter ';
    html::bold_start();
    echo 'Commentary%Astronomy';
    html::bold_end();
    html::new_paragraph();

    html::new_paragraph();


  }
  #-----------------------------------------------------
  function db_write_custom_page_button() {  # for IMPAcT, we don't need to swap between main view and alternative view
  }
  #-----------------------------------------------------
  function db_browse_reformat_data(  $column_name, $column_value  ) {

    switch( $column_name ) {

      case 'creation_timestamp':
      case 'change_timestamp':
      case 'flags':
      case 'date_of_work_std':
        $column_value = parent::db_browse_reformat_data( $column_name, $column_value );
        break;
    }

    return $column_value;
  }
  #-----------------------------------------------------

  function is_expanded_view() {
    return TRUE;  # unlike Cultures of Knowledge, IMPAcT always uses expanded view
  }
  #-----------------------------------------------------

  function overview() {

    $iwork_id = $this->read_post_parm( 'iwork_id' );
    if( ! $iwork_id ) $iwork_id = $this->read_get_parm( 'iwork_id' );

    if( ! $iwork_id ) {
      html::new_paragraph();
      echo 'This is a new record. No details have yet been saved.';
      html::new_paragraph();
      return;
    } 

    #---- Select all details for overview into properties ----
    $columns = $this->get_columns_for_overview(); # These are sometimes really fieldnames which don't necessarily
                                                  # correspond to actual columns in the database, but instead to
                                                  # decoded relationships.
    $this->select_details_for_overview( $iwork_id, $columns );

    #---- Start displaying details onscreen OR preparing CSV file ----

    $this->write_overview_stylesheet();
    $this->in_overview = TRUE;

    if( $this->parm_found_in_get( 'iwork_id' )) {  # Have gone straight to summary, rather than via 'Edit' option.
                                                   # 'Edit' option already displays work description then row of tabs

      html::h3_start();
      $this->echo_safely( $this->description );
      html::h3_end();

      html::div_start( 'class="buttonrow"' );

      if( $this->proj_edit_mode_enabled()) {
        html::form_start( PROJ_COLLECTION_WORK_CLASS, 'edit_work' );
        html::hidden_field( 'iwork_id', $this->iwork_id );
        html::submit_button( 'edit_button', 'Edit' );
        html::form_end();
      }

      html::form_start( PROJ_COLLECTION_WORK_CLASS, 'db_search' );
      html::submit_button( 'search_button', 'Search' );
      html::form_end();

      echo LINEBREAK;
      html::div_end();
      html::horizontal_rule();
    }

    $this->overview_by_email = array();
    $this->csv_output = $this->read_get_parm( 'csv_output' );

    if( ! $this->csv_output ) { # not already producing CSV output from an earlier request

      if( $this->read_session_parm( 'user_email' )) { # user has entered an email address via 'Edit your own details' 
        echo 'You can have the following summary sent to you by email as a ';
        $href = $_SERVER[ 'PHP_SELF' ] . '?iwork_id=' . $iwork_id . '&csv_output=Y';
        $title = 'Spreadsheet output of details for record no. ' . $iwork_id;
        html::link( $href, $displayed_text = 'spreadsheet', $title, $target = '_blank' ); 
      }
      else
        echo 'Note: You have not entered an email address for yourself on the system,'
             . ' so this summary cannot currently be emailed to you in spreadsheet format.'
             . " You can enter an email address via the 'Edit your own details' option of the 'Site and user data'"
             . ' menu (the last option in the Main Menu), and will then be able to receive data by email.';

      html::table_start( 'class="overview"' );
    }

    foreach( $columns as $column_name ) {

      $column_label = $this->db_get_default_column_label( $column_name );
      $column_value = $this->$column_name;

      #-----------------
      # Section headings
      #-----------------
      switch( $column_name ) {

        case 'iwork_id':
          $this->display_one_detail_of_overview( 'Status and system information', NULL, $is_heading = TRUE );
          break;

        case 'creators_for_display':
          $this->display_one_detail_of_overview( 'Author, title and subject', NULL, $is_heading = TRUE );
          break;

        case 'date_of_work_std':
          $this->display_one_detail_of_overview( 'Dates', NULL, $is_heading = TRUE );
          $hijri_date_in_words = $this->date_entity->hijri_date_in_words( 
                                    $this->date_of_work_std_year,
                                    $this->date_of_work_std_month,
                                    $this->date_of_work_std_day,
                                    $this->date_of_work2_std_year,
                                    $this->date_of_work2_std_month,
                                    $this->date_of_work2_std_day,
                                    $this->date_of_work_std_is_range );
          $this->display_one_detail_of_overview( 'Hijri date of work', $hijri_date_in_words );
          break;

        case 'places_from_for_display':
          $this->display_one_detail_of_overview( 'Places', NULL, $is_heading = TRUE );
          break;

        case 'incipit':
          $this->display_one_detail_of_overview( 'Content', NULL, $is_heading = TRUE );
          break;

        case 'related_resources':
          $this->display_one_detail_of_overview( 'Related resources', NULL, $is_heading = TRUE );
          break;

        case 'accession_code':
          $this->display_one_detail_of_overview( 'Change history', NULL, $is_heading = TRUE );
          break;
      }

      #--------------------------------
      # Display the data for one column
      #--------------------------------
      switch( $column_name ) {

        case 'original_calendar':
          $column_value = $this->date_entity->decode_calendar( $column_value );
          if( $column_value == 'Unknown' ) $column_value = '';
          $this->display_one_detail_of_overview( $column_label, $column_value ) ;
          break;

        case 'date_of_work_std_is_range':
        case 'date_of_work_inferred':
        case 'date_of_work_uncertain':
        case 'date_of_work_approx':
        case 'authors_inferred':
        case 'authors_uncertain':
        case 'addressees_inferred':
        case 'addressees_uncertain':
        case 'destination_inferred':
        case 'destination_uncertain':
        case 'origin_inferred':
        case 'origin_uncertain':
        case 'work_is_translation':
        case 'work_to_be_deleted':
          if( $column_value )
            $column_value = '*** Yes ***';
          else
            $column_value = 'No';
          $this->display_one_detail_of_overview( $column_label, $column_value, FALSE, TRUE ) ;

          break;

        case 'change_timestamp':
        case 'creation_timestamp':
          $column_value = substr( $column_value, 0, strlen( 'yyyy-mm-dd hh:mi' ));
          $this->display_one_detail_of_overview( $column_label, $column_value ) ;
          break;

        case 'edit_status':
          if( $column_value == '' ) $column_value = 'New';
          $this->display_one_detail_of_overview( $column_label, $column_value ) ;
          break;

        case 'change_user':
          $this->display_one_detail_of_overview( $column_label, $column_value ) ;

          if( $this->proj_edit_mode_enabled()) { # non-editors don't need to poke around in the audit trail
            html::tablerow_start();
            html::tabledata( '' );
            html::tabledata_start( 'class="fieldvalue"' );
            $this->audit_trail_link();
            html::tabledata_end();
            html::tablerow_end();
          }
          break;

        case 'manifestations': # just a dummy column, as we want to do a detailed display of each one separately
          $this->overview_of_manifestations();
          break;

        default:
          $this->display_one_detail_of_overview( $column_label, $column_value ) ;
      }

      #-------------------------------
      # In some cases add a blank line
      #-------------------------------
      switch( $column_name ) {

        case 'language_of_work':
        case 'keywords':
          $this->blank_line_in_overview();
          break;

        default:
          if( $this->string_starts_with( $column_name, 'bibliographic_references_and_other_notes' )) {
            $this->blank_line_in_overview();
          }
          break;
      }
    }


    if( $this->csv_output ) {
      $msg_subject = 'Work ' . $this->iwork_id . ': ' . $this->description;

      $this->db_produce_csv_output( $this->overview_by_email,
                                    $msg_recipient = NULL, # by default send file to self
                                    $msg_body = NULL,      # use default
                                    $msg_subject );
    }
    else # not in CSV output mode
      html::table_end();

  }
  #-----------------------------------------------------

  function get_columns_for_overview() {  # Arranged in same order as fields in editing form


    $columns = array(  # Not using 'DB list columns' here, as would like to arrange in different order,
                       # AND add columns from view, AND add extra relationship decodes.
      'iwork_id',
      'edit_status',
      'work_to_be_deleted',
      'manif_count',

      'creators_for_display',
      'authors_as_marked',
      'authors_inferred',
      'authors_uncertain',
      'bibliographic_references_and_other_notes_on_author',

      'work_title',
      'work_alternative_titles',
      'bibliographic_references_and_other_notes_on_title_of_work',

      'type_of_work',
      'subjects',
      'bibliographic_references_and_other_notes_on_type_or_subject_of_work',

      'language_of_work',

      'patrons',
      'bibliographic_references_and_other_notes_on_patrons',

      'dedicatees',
      'bibliographic_references_and_other_notes_on_dedicatees',

      'basis_text_commented_on_etc',
      'bibliographic_references_and_other_notes_on_basis_text',

      'commentaries_etc',
      'bibliographic_references_and_other_notes_on_commentaries_etc',

      'addressees',
      'addressees_as_marked',
      'addressees_inferred',
      'addressees_uncertain',
      'bibliographic_references_and_other_notes_on_addressees',

      'date_of_work_std',
      'date_of_work_std_gregorian',
      'date_of_work_as_marked',
      'original_calendar',
      'date_of_work_std_is_range',
      'date_of_work_inferred',
      'date_of_work_uncertain',
      'date_of_work_approx',
      'bibliographic_references_and_other_notes_on_date_of_work',

      'places_from_for_display',
      'origin_as_marked',
      'origin_inferred',
      'origin_uncertain',
      'bibliographic_references_and_other_notes_on_place_of_composition',

      'places_to_for_display',
      'destination_as_marked',
      'destination_inferred',
      'destination_uncertain',

      'incipit',
      'bibliographic_references_and_other_notes_on_incipit',

      'explicit',
      'bibliographic_references_and_other_notes_on_explicit',

      'ps',
      'bibliographic_references_and_other_notes_on_colophon',

      'abstract',
      'keywords',
      'associated_people',
      'bibliographic_references_and_other_notes_on_associated_people',
      'associated_places',
      'bibliographic_references_and_other_notes_on_associated_places',
      'associated_primary_sources',
      'bibliographic_references_and_other_notes_on_associated_primary_sources',
      'general_notes',

      'manifestations',

      'related_resources',

      'accession_code',
      'creation_timestamp',
      'creation_user',
      'change_timestamp',
      'change_user'
    );

    return $columns;
  }
  #-----------------------------------------------------

  function select_details_for_overview( $iwork_id, $columns ) {

    if( ! $iwork_id ) die( 'Invalid work ID.' );
    if( ! $columns ) die( 'Invalid columns.' );

    $this->set_work( $iwork_id );  # Get every column from the work table

    # Now get selected columns from work view
    $statement = $this->get_view_select_for_overview( $iwork_id );
    if( $statement ) $this->db_select_into_properties( $statement );  # In some cases this might overwrite a detail
                                                                      # from the work table, e.g. with a decode.
    # Now get decoded relationships into specified fieldnames
    $this->decode_rels_for_overview( $columns );

    # Get manifestation IDs for this work
    $this->manif_ids = $this->get_manifestation_ids();
    $this->manif_obj = $this->manifest_obj; # parent class uses '$this->manif_obj'
  }
  #-----------------------------------------------------

  function get_view_select_for_overview( $iwork_id ) {  # Make sure you don't try to select columns
                                                        # that don't exist in the appropriate view!
    if( ! $iwork_id ) die( 'Invalid input.' );

    $cols = DBEntity::db_list_columns( $this->proj_work_viewname() );
    $viewcols = array();

    foreach( $cols as $crow ) {
      $column_name = $crow[ 'column_name' ];
      $viewcols[] = $column_name;
    }

    $selection_cols = '';

    $potential_selection_cols = array( 'creators_for_display',
                                       'type_of_work',
                                       'subjects',
                                       'language_of_work',
                                       'addressees_for_display',
                                       'places_from_for_display',
                                       'places_to_for_display',
                                       'related_works',
                                       'related_resources',
                                       'manif_count',
                                       'edit_status' );

    foreach( $potential_selection_cols as $col ) {
      if( in_array( $col, $viewcols )) {
        if( $selection_cols > '' ) $selection_cols .= ', ';
        $selection_cols .= $col;
      }
    }

    if( ! $selection_cols ) return NULL;

    $statement = 'select ' . $selection_cols
               . ' from ' . $this->proj_work_viewname() . " where iwork_id = $iwork_id";
    return $statement;
  }
  #-----------------------------------------------------

  function decode_rels_for_overview( $columns ) {

    if( ! is_array( $columns )) die( 'Invalid input' );
    $comment_settings = array(
      'left_table_name' => $this->proj_comment_tablename(),
      'reltypes'        => array( RELTYPE_COMMENT_REFERS_TO_ENTITY => 'Comment on work' ),
      'right_table_name'=> $this->proj_work_tablename(),
      'side_to_get'     => 'left'
    );

    foreach( $columns as $colname ) {
      #--------------------------------------------------------------------
      # Find out which relationship types to check for each group of fields.
      #--------------------------------------------------------------------
      # In some cases we've got an array set up in the Relationship object, defining the relationship types
      # that appear within each fieldset (such as 'Commentaries, continuations etc') on the form.
      # However, comments and related resources aren't set up in that way in Relationship, so handle differently.

      $rel_settings = NULL;
      switch( $colname ) {

        case 'bibliographic_references_and_other_notes_on_author':
          $rel_settings = $comment_settings;
          $rel_settings[ 'reltypes' ] = array( RELTYPE_COMMENT_REFERS_TO_AUTHOR => 'Comment' );
          break;

        case 'bibliographic_references_and_other_notes_on_title_of_work':
          $rel_settings = $comment_settings;
          $rel_settings[ 'reltypes' ] = array( RELTYPE_COMMENT_REFERS_TO_TITLE_OF_WORK => 'Comment' );
          break;

        case 'bibliographic_references_and_other_notes_on_type_or_subject_of_work':
          $rel_settings = $comment_settings;
          $rel_settings[ 'reltypes' ] = array( RELTYPE_COMMENT_REFERS_TO_TYPE_OF_WORK => 'Comment' );
          break;

        case 'patrons':
          $rel_settings = $this->rel_obj->get_relationship_field_setting( FIELDSET_PATRONS_OF_WORK );
          break;

        case 'bibliographic_references_and_other_notes_on_patrons':
          $rel_settings = $comment_settings;
          $rel_settings[ 'reltypes' ] = array( RELTYPE_COMMENT_REFERS_TO_PATRONS_OF_WORK => 'Comment' );
          break;

        case 'dedicatees':
          $rel_settings = $this->rel_obj->get_relationship_field_setting( FIELDSET_DEDICATEES_OF_WORK );
          break;

        case 'bibliographic_references_and_other_notes_on_dedicatees':
          $rel_settings = $comment_settings;
          $rel_settings[ 'reltypes' ] = array( RELTYPE_COMMENT_REFERS_TO_DEDICATEES_OF_WORK => 'Comment' );
          break;

        case 'basis_text_commented_on_etc':
          $rel_settings = $this->rel_obj->get_relationship_field_setting( FIELDSET_WORK_DISCUSSED );
          break;

        case 'bibliographic_references_and_other_notes_on_basis_text':
          $rel_settings = $comment_settings;
          $rel_settings[ 'reltypes' ] = array( RELTYPE_COMMENT_REFERS_TO_BASIS_TEXTS_OF_WORK => 'Comment' );
          break;

        case 'commentaries_etc':
          $rel_settings = $this->rel_obj->get_relationship_field_setting( FIELDSET_WORK_DISCUSSING );
          break;

        case 'bibliographic_references_and_other_notes_on_commentaries_etc':
          $rel_settings = $comment_settings;
          $rel_settings[ 'reltypes' ] = array( RELTYPE_COMMENT_REFERS_TO_COMMENTARIES_ON_WORK => 'Comment' );
          break;

        case 'addressees':
          $rel_settings = $this->rel_obj->get_relationship_field_setting( FIELDSET_ADDRESSEE );
          break;

        case 'bibliographic_references_and_other_notes_on_addressees':
          $rel_settings = $comment_settings;
          $rel_settings[ 'reltypes' ] = array( RELTYPE_COMMENT_REFERS_TO_ADDRESSEE => 'Comment' );
          break;

        case 'bibliographic_references_and_other_notes_on_date_of_work':
          $rel_settings = $comment_settings;
          $rel_settings[ 'reltypes' ] = array( RELTYPE_COMMENT_REFERS_TO_DATE => 'Comment' );
          break;

        case 'bibliographic_references_and_other_notes_on_place_of_composition':
          $rel_settings = $comment_settings;
          $rel_settings[ 'reltypes' ] = array( RELTYPE_COMMENT_REFERS_TO_PLACE_OF_COMPOSITION_OF_WORK => 'Comment' );
          break;

        case 'bibliographic_references_and_other_notes_on_incipit':
          $rel_settings = $comment_settings;
          $rel_settings[ 'reltypes' ] = array( RELTYPE_COMMENT_REFERS_TO_INCIPIT_OF_WORK => 'Comment' );
          break;

        case 'bibliographic_references_and_other_notes_on_explicit':
          $rel_settings = $comment_settings;
          $rel_settings[ 'reltypes' ] = array( RELTYPE_COMMENT_REFERS_TO_EXCIPIT_OF_WORK => 'Comment' );
          break;

        case 'bibliographic_references_and_other_notes_on_colophon':
          $rel_settings = $comment_settings;
          $rel_settings[ 'reltypes' ] = array( RELTYPE_COMMENT_REFERS_TO_COLOPHON_OF_WORK => 'Comment' );
          break;

        case 'associated_people':
          $rel_settings = $this->rel_obj->get_relationship_field_setting( FIELDSET_PEOPLE_MENTIONED );
          break;

        case 'bibliographic_references_and_other_notes_on_associated_people':
          $rel_settings = $comment_settings;
          $rel_settings[ 'reltypes' ] = array( RELTYPE_COMMENT_REFERS_TO_PEOPLE_MENTIONED_IN_WORK => 'Comment' );
          break;

        case 'associated_places':
          $rel_settings = $this->rel_obj->get_relationship_field_setting( FIELDSET_PLACES_MENTIONED );
          break;

        case 'bibliographic_references_and_other_notes_on_associated_places':
          $rel_settings = $comment_settings;
          $rel_settings[ 'reltypes' ] = array( RELTYPE_COMMENT_REFERS_TO_PLACES_MENTIONED_IN_WORK => 'Comment' );
          break;

        case 'associated_primary_sources':
          $rel_settings = $this->rel_obj->get_relationship_field_setting( FIELDSET_WORKS_MENTIONED );
          break;

        case 'bibliographic_references_and_other_notes_on_associated_primary_sources':
          $rel_settings = $comment_settings;
          $rel_settings[ 'reltypes' ] = array( RELTYPE_COMMENT_REFERS_TO_WORKS_MENTIONED_IN_WORK => 'Comment' );
          break;

        case 'general_notes':
          $rel_settings = $comment_settings;
          $rel_settings[ 'reltypes' ] = array( RELTYPE_COMMENT_REFERS_TO_ENTITY => 'Comment' );
          break;
      }

      if( ! $rel_settings ) continue;
      extract( $rel_settings, EXTR_OVERWRITE );
      $reltype_count = count( $reltypes );

      #-------------------------------------------------------------------------------------
      # We might want to look at a fieldset left-to-right, right-to-left, or even both ways.
      #-------------------------------------------------------------------------------------
      if( $side_to_get == 'left' )
        $arrays_to_check = array( 'left' => 'this_on_right' );
      elseif( $side_to_get == 'right' ) 
        $arrays_to_check = array( 'right' => 'this_on_left' );
      elseif( $side_to_get == 'both' ) 
        $arrays_to_check = array( 'right' => 'this_on_left', 'left' => 'this_on_right' );
      else
        $arrays_to_check = NULL; # this should never happen

      $reltypes_in_use = 0;
      $this->$colname = NULL;

      #-----------------------------------------------------------------------------
      # Write out the data for each relationship type in a fieldset as an HTML list,
      # looking at each relationship type one by one.
      #-----------------------------------------------------------------------------
      foreach( $reltypes as $reltype => $field_label ) {

        foreach( $arrays_to_check as $side_to_get => $array_to_check ) {
          $rel_label = '';
          if( $reltype_count > 1 ) {  # no need to repeat the main field label if there's only one relationship type
            if( $side_to_get == 'left' )
              $decode_col = 'desc_right_to_left';
            else
              $decode_col = 'desc_left_to_right';
            $statement = "select $decode_col from " . $this->proj_relationship_type_tablename()
                       . " where relationship_code = '$reltype'";
            $rel_label = $this->db_select_one_value( $statement );
            $rel_label = ucfirst( $rel_label );
          }

          $rels = array();
          $rel_summary = '';
          $rels_of_this_type = 0;
          
          #-----------------------------------------------------
          # First identify all relationships of the current type
          #-----------------------------------------------------
          foreach( $this->$array_to_check as $row ) {
            extract( $row, EXTR_OVERWRITE );
            if( $relationship_type == $reltype ) {
              $rels[] = $row;
              $rels_of_this_type++;
            }
          }

          #------------------------------------------------
          # If there are some relationships of this type...
          #------------------------------------------------
          if( $rels_of_this_type > 0 ) {
            $reltypes_in_use++;

            if( $rels_of_this_type > 1 ) {
              $rel_summary = '<ul>';
            }
            foreach( $rels as $row ) {
              extract( $row, EXTR_OVERWRITE );
              if( $side_to_get == 'left' ) {
                $other_table = $left_table_name;
                $other_id = $left_id_value;
              }
              elseif( $side_to_get == 'right' ) {
                $other_table = $right_table_name;
                $other_id = $right_id_value;
              }
              #-----------------------------------------------------------------
              # Decode the other side of the relationship and add it to the list
              #-----------------------------------------------------------------
              $func = $this->proj_database_function_name( 'decode', $include_collection_code = TRUE );
              $statement = "select $func( '$other_table', '$other_id', 0 )";  # 0 means don't suppress links
              $decode = $this->db_select_one_value( $statement );

              if( $rels_of_this_type > 1 ) {
                $decode = '<li>' . $decode . '</li>';
              }
              $rel_summary .= $decode;
            }
            if( $rels_of_this_type > 1 ) {
              $rel_summary .= '</ul>';
            }
          }

          if( $rel_summary != '' ) {
            if( $reltypes_in_use > 1 ) $this->$colname .= '</li><li>';

            if( $rel_label != '' ) {
              $this->$colname .= $rel_label . ': ';
            }

            $this->$colname .= $rel_summary;
          }
        }
      }

      #---------------------------------------------------------------------
      # If necessary make the list of relationship types into an outer list.
      #---------------------------------------------------------------------
      if( $reltypes_in_use > 1 ) $this->$colname = '<ul><li>' . $this->$colname . '</li></ul>';
    }
  }
  #-----------------------------------------------------

  function display_one_detail_of_overview( $column_label, $column_value, $is_heading = FALSE, $is_flag = FALSE ) {

    if( $is_flag && ( $column_value == 'No' || $column_value == '' )) return;

    parent::display_one_detail_of_overview( $column_label, $column_value, $is_heading );
  }
  #-----------------------------------------------------

  function blank_line_in_overview() {

    if( ! $this->csv_output ) {
      html::tablerow_start();
      html::tabledata( NULL );
      html::tabledata( NULL );
      html::tablerow_end();
    }
  }
  #-----------------------------------------------------

  function validate_parm( $parm_name ) {  # overrides parent method

    switch( $parm_name ) {

      case 'people_and_places_associated_with_manifestations':
        return $this->is_ok_free_text( $this->parm_value );
        
      case 'title_of_work_inferred':
      case 'title_of_work_uncertain':
      case 'title_of_work_unknown':
        if( $this->reading_parms_for_update )
          return $this->is_integer( $this->parm_value );
        else
          return $this->is_alphabetic_or_blank( $this->parm_value );

      default:
        return parent::validate_parm( $parm_name );
    }
  }
  #-----------------------------------------------------
}
?>
