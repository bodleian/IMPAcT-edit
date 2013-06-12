<?php

# Subversion repository: https://damssupport.bodleian.ox.ac.uk/svn/impact/php
# Author: Sushila Burgess
#====================================================================================


#----- Extra calendar types -----------

define( 'CALENDAR_TYPE_ISLAMIC_LUNAR',      'IL' );
define( 'CALENDAR_TYPE_ISLAMIC_LUNAR_DESC', 'Islamic lunar' );

define( 'CALENDAR_TYPE_ISLAMIC_SOLAR',      'IS' );
define( 'CALENDAR_TYPE_ISLAMIC_SOLAR_DESC', 'Islamic solar' );

define( 'CALENDAR_TYPE_ALEXANDRIAN',      'AL' );
define( 'CALENDAR_TYPE_ALEXANDRIAN_DESC', 'Alexandrian' );

define( 'CALENDAR_TYPE_RUMI',      'RU' );
define( 'CALENDAR_TYPE_RUMI_DESC', 'Rumi' );

#----- End extra calendar types -----------


class Islamic_Date_Entity extends Date_Entity {

  #----------------------------------------------------------------------------------

  function Islamic_Date_Entity( &$db_connection, $date_format = 'dd/mm/yyyy' ) {

    #-----------------------------------------------------
    # Check we have got a valid connection to the database
    #-----------------------------------------------------
    $this->Date_Entity( $db_connection, $date_format );
  }

  #----------------------------------------------------------------------------------

  function set_month_list() {

    $this->month_list = array( 0 => '',
                               1 => 'Muharram',
                               2 => 'Safar',
                               3 => 'Rabi` I',
                               4 => 'Rabi` II',
                               5 => 'Jumada I',
                               6 => 'Jumada II',
                               7 => 'Rajab',
                               8 => 'Sha`ban',
                               9 => 'Ramadan',
                               10 => 'Shawwal',
                               11 => 'Dhu al-Qa `da',
                               12 => 'Dhu al-Hijja' );
  }
  #----------------------------------------------------------------------------------

  function set_calendar_list() {

    $this->calendar_list = array( CALENDAR_TYPE_UNKNOWN    => CALENDAR_TYPE_UNKNOWN_DESC,
                                  CALENDAR_TYPE_ISLAMIC_LUNAR => CALENDAR_TYPE_ISLAMIC_LUNAR_DESC,
                                  CALENDAR_TYPE_ISLAMIC_SOLAR => CALENDAR_TYPE_ISLAMIC_SOLAR_DESC,
                                  CALENDAR_TYPE_ALEXANDRIAN   => CALENDAR_TYPE_ALEXANDRIAN_DESC,
                                  CALENDAR_TYPE_RUMI          => CALENDAR_TYPE_RUMI_DESC,
                                  CALENDAR_TYPE_GREG       => CALENDAR_TYPE_GREG_DESC,
                                  CALENDAR_TYPE_JULIAN_MAR => CALENDAR_TYPE_JULIAN_MAR_DESC,
                                  CALENDAR_TYPE_JULIAN_JAN => CALENDAR_TYPE_JULIAN_JAN_DESC,
                                  CALENDAR_TYPE_OTHER      => CALENDAR_TYPE_OTHER_DESC );
  }
  #----------------------------------------------------------------------------------

  function date_entry_fieldset( $fields, $calendar_field, $legend, $extra_msg = NULL, 
                                $hide_sortable_dates = FALSE, $include_uncertainty_flags = FALSE,
                                $date_range_help = array( DATE_RANGE_HELP_1, DATE_RANGE_HELP_2 ),
                                $display_calendars_in_main_fieldset = FALSE ) {

    echo '<script src="islamic_christian_date_converter.js"></script>' . NEWLINE;

    if(  $this->convert_start_and_end_dates_to_ce && $this->write_conversion_script ) {
      $this->write_script_to_convert_start_and_end_dates_to_ce();
      $this->write_script_to_show_or_hide_ce_end_date();
    }

    parent::date_entry_fieldset( $fields, $calendar_field, $legend, $extra_msg, 
                                 $hide_sortable_dates, $include_uncertainty_flags,
                                 $date_range_help, $display_calendars_in_main_fieldset );

    echo LINEBREAK;
    html::italic_start();
    html::link( 'http://www.oriold.uzh.ch/static/hegira.html',
                'Hijri to CE date conversion algorithm',
                'Hijri to CE date conversion algorithm by Johannes Thomann',
                '_blank' );
    echo ' &copy; J. Thomann 1996. This version of the algorithm used by kind permission of Johannes Thomann.' ;
    html::italic_end();
    echo LINEBREAK;
  }
  #----------------------------------------------------------------------------------


  function sortable_date_entry() {

    html::bold_start();
    echo 'Dates for ordering:';
    html::bold_end();
    html::new_paragraph();

    html::italic_start();
    echo "The 'dates for ordering' are used purely to sort works etc into chronological order."
         . ' If entering the CE date manually, please note: ';
    html::ulist_start();
    html::listitem( 'If the year is unknown, please enter 9999 in order to position undated records'
                    . ' at the end of the list.' );
    html::listitem( 'If the month and day are unknown, you can enter a default value of 01-01.' );
    html::ulist_end();

    html::italic_end();
    html::new_paragraph();

    $this->sortable_date_field( $this->sortable_date_orig_calendar,
                                'Hijri',
                                $this->get_property_value( $this->sortable_date_orig_calendar ),
                                $first = TRUE );

    echo ' ';

    $this->sortable_date_field( $this->sortable_date_gregorian,
                                'CE (yyyy-mm-dd)',
                                $this->get_property_value( $this->sortable_date_gregorian ));
    echo ' ';

    $this->sortable_date_manual_entry();  # they always have to enter it manually at the moment

    html::new_paragraph();
  }
  #----------------------------------------------------------------------------------

  function sortable_date_field( $fieldname, $label, $field_value, $first = FALSE ) {

    if( $first ) {  # date in original calendar, auto-generated
      $tabindex = 0;
      $parms = 'READONLY';
    }
    else { # Christian calendar, manually entered
      html::span_start( 'class="nextfield"' );
      $tabindex = 1;
      $parms = NULL;
    }

    html::input_field( $fieldname, $label, $field_value, FALSE, $size = STD_DATE_INPUT_FIELD_SIZE,
                       $tabindex, NULL, NULL, $input_parms = $parms );

    if( ! $first ) html::span_end( 'nextfield' );
  }
  #----------------------------------------------------------------------------------

  function sortable_date_manual_entry() {
    html::hidden_field( $this->sortable_date_manual_entry, NULL );
  }
  #----------------------------------------------------------------------------------

  function calendar_selection_within_main_fieldset() { # need to squeeze in some extra text-based fields

    parent::calendar_selection_within_main_fieldset();

    $fieldname = $this->date_in_words_id;
    if( ! $fieldname ) return;

    $field_value = $this->$fieldname;

    html::input_field( $fieldname, $label = 'Date in original calendar', $value = $field_value, $in_table = FALSE, 
                       $size = FLD_SIZE_DATE_AS_MARKED );
    html::new_paragraph();
    html::italic_start();
    echo 'This field can be used to enter dates from the less standard calendars such as Alexandrian,';
    echo LINEBREAK;
    echo ' or can be used in addition to the Hijri and CE dates to give the date in words as well as numbers.';
    html::italic_end();
    html::new_paragraph();
    html::horizontal_rule();
  }
  #----------------------------------------------------------------------------------

  function is_postgres_timestamp( $parm_value, $allow_blank = TRUE ) {

    $is_postgres_timestamp = parent::is_postgres_timestamp( $parm_value, $allow_blank );

    if( ! $is_postgres_timestamp ) {  # perhaps we are dealing with a 3-figure year from Islamic calendar
      $parts = explode( '-', $parm_value );
      if( count( $parts ) == 3 ) {
        $year = $parts[ 0 ];
        if( $this->is_integer( $year )) {
          $year = ltrim( $year, '0' );
          $year = intval( $year );
          if( $year < 1000 ) {
            $year += 1000;
            $date_string = $year . '-' . $parts[1] . '-' . $parts[2];
            return parent::is_postgres_timestamp( $date_string, $allow_blank );
          }
        }
      }
    }

    return $is_postgres_timestamp;
  }
  #-----------------------------------------------------

  function yyyy_to_dd_mm_yyyy( $parm_name, $parm_value, $write_to_post = TRUE ) {

    $parm_value = trim( $parm_value );

    if( $this->is_integer( $parm_value )) {  # allow for years such as '783' rather than '0783'
      if( strlen( $parm_value ) > 0 && strlen( $parm_value ) < strlen( 'yyyy' )) {
        $parm_value = str_pad( $parm_value, strlen( 'yyyy' ), '0', STR_PAD_LEFT );
      }
    }

    return parent::yyyy_to_dd_mm_yyyy( $parm_name, $parm_value, $write_to_post );
  }
  #-----------------------------------------------------

  function write_gregorian_script() {

    $script_name = $this->generate_sortable_dates_script . '_greg';

    $script  = 'function ' . $script_name . '( sortable_date ) { '               . NEWLINE;

    #============ Find out which values have been entered =================================
    $script .= '  var date_parts = sortable_date.split("-");'                    . NEWLINE;
    $script .= '  var partcount = date_parts.length;'                            . NEWLINE;
    $script .= '  if( partcount != 3 ) {'                                        . NEWLINE;
    $script .= '    alert("Invalid date value in " + partcount + " parts");'     . NEWLINE;
    $script .= '    return;'                                                     . NEWLINE;
    $script .= '  } '                                                            . NEWLINE;

    $script .= '  var year_val = date_parts[0];'                                 . NEWLINE;
    $script .= '  var month_val = date_parts[1] '                                . NEWLINE;
    $script .= '  var day_val = date_parts[2];'                                  . NEWLINE;

    $script .= '  if( month_val.substr( 0, 1 ) == "0" ) { '                      . NEWLINE;
    $script .= '    month_val = month_val.substr( 1, 1 );'                       . NEWLINE;
    $script .= '  } '                                                            . NEWLINE;

    $script .= '  if( day_val.substr( 0, 1 ) == "0" ) { '                        . NEWLINE;
    $script .= '    day_val = day_val.substr( 1, 1 );'                           . NEWLINE;
    $script .= '  } '                                                            . NEWLINE;

    $script .= '  year_val = parseInt( year_val );'                              . NEWLINE;
    $script .= '  month_val = parseInt( month_val );'                            . NEWLINE;
    $script .= '  day_val = parseInt( day_val );'                                . NEWLINE;

    $script .= '  var result;'                                                   . NEWLINE;


    #============ Either: skip calculation if year is unknown or invalid ==================================
    $script .= '  if( year_val < 100 || ( year_val > 1500 && year_val != 9999 )) {'         . NEWLINE;
    $script .= '    alert("Enter Hijri year as yyy or yyyy (not greater than 1500).");'     . NEWLINE;
    $script .= '    var year_field = document.getElementById( "' .  $this->year_id . '" );' . NEWLINE;
    $script .= '    var hijri_date_field = document.getElementById( "' .  $this->sortable_date_orig_calendar . '" );' 
                                                                                 . NEWLINE;
    $script .= '    var CE_date_field = document.getElementById( "' .  $this->sortable_date_gregorian . '" );' 
                                                                                 . NEWLINE;
    $script .= '    year_field.value = "";'                                      . NEWLINE;
    $script .= '    hijri_date_field.value = "9999-12-31";'                      . NEWLINE;
    $script .= '    CE_date_field.value = "9999-12-31";'                         . NEWLINE;
    $script .= '    return;'                                                     . NEWLINE;
    $script .= '  }'                                                             . NEWLINE;

    $script .= '  if( year_val == 9999 ) {'                                      . NEWLINE;
    $script .= '    result = year_val + "-";'                                    . NEWLINE;
    $script .= '    if( month_val < 10 ) {'                                      . NEWLINE;
    $script .= '      result = result + "0";'                                    . NEWLINE;
    $script .= '    }'                                                           . NEWLINE;
    $script .= '    result = result + month_val + "-";'                          . NEWLINE;
    $script .= '    if( day_val < 10 ) {'                                        . NEWLINE;
    $script .= '      result = result + "0";'                                    . NEWLINE;
    $script .= '    }'                                                           . NEWLINE;
    $script .= '    result = result + day_val;'                                  . NEWLINE;
    $script .= '  }'                                                             . NEWLINE;


    #======================================================================================================
    #============ Or: calculate CE equivalent of the Hijri date using Johannes Thomann's script ===========
    #======================================================================================================
    $script .= '  else {'                                                        . NEWLINE;
    $script .= '    result = islToChr( year_val, month_val, day_val );'          . NEWLINE;
    $script .= '  }'                                                           . NEWLINE;


    #============ Put the result into the CE date field ===================================================
    $script .= '  var result_field = document.getElementById( "' .  $this->sortable_date_gregorian . '" );'
                                                                                 . NEWLINE;
    $script .= '  if( result_field != null ) {'                                  . NEWLINE;
    $script .= '    result_field.value = result;'                                . NEWLINE;
    $script .= '  } '                                                            . NEWLINE;
    $script .= '} '                                                              . NEWLINE;

    html::write_javascript_function( $script );

    return $script_name;
  }
  #----------------------------------------------------------------------------------




  #================================================================================
  #============  Date conversion functions (first attempt) ========================
  # Convert Islamic dates to CE calendar, adapted from source code in Visual Basic by Kees Cowprie,
  # itself based on C programs by Dr Waleed A. Muhanna.
  # The adaptation consists of converting Visual Basic to PHP. No change is made to the algorithm.
  # See: http://members.casema.nl/couprie/calmath/islamic/islamic_jdn.html
  # and: http://members.casema.nl/couprie/calmath/index.html
  # and: http://fisher.osu.edu/~muhanna_1/IslamicTimer.html and http://fisher.osu.edu/~muhanna_1/Muhanna.html
  #=================================================================================
  # THE FOLLOWING ARE NO LONGER USED EXCEPT IN 'CALENDAR TOOLS' OPTION OF MAIN MENU.
  #=================================================================================

  function islamic_date_to_julian_day_number( $iYear, $iMonth, $iDay ) {
    
    # NMONTH is the number of months between Julian day number 1 and
    # the year 1405 A.H. which started immediately after lunar
    # conjunction number 1048 which occurred on September 1984 25d
    # 3h 10m UT.

    $NMONTHS = (1405 * 12 + 1);

    $k = 0;
    
    if ( $iYear < 0) $iYear = $iYear + 1;

    $k = $iMonth + $iYear * 12 - $NMONTHS; # nunber of months since 1/1/1405

    return intval( $this->visibility( $k + 1048 ) + $iDay + 0.5 );
  }
  #------------------------------------------------------------------------------------------------------

  function visibility( $n ) {
    
    # parameters for Makkah: for a new moon to be visible after sunset on
    # a the same day in which it started, it has to have started before
    # (SUNSET-MINAGE)-TIMZ=3 A.M. local time.

    $TIMZ = 3;
    $MINAGE = 13.5;
    $SUNSET = 19.5; # approximate
    $TIMDIF = ($SUNSET - $MINAGE);

    $jd = 0.0;
    $tf = 0.0;
    $d = 0;
    
    $jd = $this->tmoonphase( $n, 0 );

    $d = intval( $jd );
    $tf = $jd - $d;

    if ( $tf <= 0.5 ) {  # new moon starts in the afternoon
    $visibility = ($jd + 1 );
    }
    else {  # new moon starts before noon
      $tf = ( $tf - 0.5 ) * 24 + $TIMZ; # local time
      if ($tf > $TIMDIF) {
        $visibility = ( $jd + 1 ); # age at sunset < min for visiblity
      }
      else {
        $visibility = $jd;
      }
    }
    return $visibility;
  }
  #------------------------------------------------------------------------------------------------------

  ## tmoonphase
  ## Description
  ## Given an integer _n_ and a phase selector (nph=0,1,2,3 for new,first,full,last quarters respectively, 
  ## this function returns the Julian date/time (integer part is the julian day number, fraction is the time) 
  ## of the Nth such phase since January 1900.
  ## Gory Details
  ## This routine is based on an adaptation from "Astronomical  Formulae for Calculators" by Jean Meeus, 
  ## Third Edition, Willmann-Bell, 1985.
  ## Code section

  function tmoonphase( $n, $nph ) {
    
    $RPD = 1.74532925199433E-02; # radians per degree (pi/180)

    $jd = 0.0;
    $t = 0.0;
    $t2 = 0.0;
    $t3 = 0.0;
    $k = 0.0;
    $ma = 0.0;
    $sa = 0.0;
    $tf = 0.0;
    $xtra = 0.0;

    $k = $n + $nph / 4;
    $t = $k / 1236.85;
    $t2 = $t * $t;
    $t3 = $t2 * $t;
    $jd = 2415020.75933 + 29.53058868 * $k - 0.0001178 * t2  
      - 0.000000155 * $t3  
      + 0.00033 * Sin($RPD * (166.56 + 132.87 * $t - 0.009173 * $t2));
  #
  # Sun's mean anomaly
    $sa = $RPD * (359.2242 + 29.10535608 * $k - 0.0000333 * $t2 - 0.00000347 * $t3);
  #
  # Moon's mean anomaly
    $ma = $RPD * (306.0253 + 385.81691806 * $k + 0.0107306 * $t2 + 0.00001236 * $t3);
    
  #
  # Moon's argument of latitude
    $tf = $RPD * 2 * (21.2964 + 390.67050646 * $k - 0.0016528 * $t2  
          - 0.00000239 * $t3);
  #
  # should reduce to interval 0-1.0 before calculating further
    switch( $nph ) {
    case 0:
    case 2:
      $xtra = (0.1734 - 0.000393 * $t) * Sin($sa)  
            + 0.0021 * Sin($sa * 2)  
            - 0.4068 * Sin($ma) + 0.0161 * Sin(2 * $ma) - 0.0004 * Sin(3 * $ma)  
            + 0.0104 * Sin($tf)  
            - 0.0051 * Sin($sa + $ma) - 0.0074 * Sin($sa - $ma)  
            + 0.0004 * Sin($tf + $sa) - 0.0004 * Sin($tf - $sa)  
            - 0.0006 * Sin($tf + $ma) + 0.001 * Sin($tf - $ma)  
            + 0.0005 * Sin($sa + 2 * $ma);
      break;

    case 1:
    case 3:
      $xtra = (0.1721 - 0.0004 * $t) * Sin($sa)  
            + 0.0021 * Sin($sa * 2)  
            - 0.628 * Sin($ma) + 0.0089 * Sin(2 * $ma) - 0.0004 * Sin(3 * $ma)  
            + 0.0079 * Sin($tf)  
            - 0.0119 * Sin($sa + $ma) - 0.0047 * Sin($sa - $ma)  
            + 0.0003 * Sin($tf + $sa) - 0.0004 * Sin($tf - $sa)  
            - 0.0006 * Sin($tf + $ma) + 0.0021 * Sin($tf - $ma)  
            + 0.0003 * Sin($sa + 2 * $ma) + 0.0004 * Sin($sa - 2 * $ma)  
            - 0.0003 * Sin(2 * $sa + $ma);
      if ($nph == 1) {
        $xtra = $xtra + 0.0028 - 0.0004 * Cos($sa) + 0.0003 * Cos($ma);
      }
      else {
        $xtra = $xtra - 0.0028 + 0.0004 * Cos($sa) - 0.0003 * Cos($ma);
      }
      break;

    default:
      return 0;
    }

  # convert from Ephemeris Time (ET) to (approximate)Universal Time (UT)
    $tmoonphase = $jd + $xtra - (0.41 + 1.2053 * $t + 0.4992 * $t2) / 1440;

    return $tmoonphase;
  }
  #------------------------------------------------------------------------------------------------------

  function julian_day_number_to_date( $jdn ) {

    $iYear  = 0;
    $iMonth = 0;
    $iDay   = 0;

    $l = 0;
    $k = 0;
    $n = 0;
    $i = 0;
    $j = 0;

    $j = $jdn + 1402;
    $k = floor(($j - 1) / 1461);
    $l = $j - 1461 * $k;
    $n = floor(($l - 1) / 365) - floor($l / 1461);
    $i = $l - 365 * $n + 30;
    $j = floor((80 * $i) / 2447);
    $iDay = $i - floor((2447 * $j) / 80);
    $i = floor($j / 11);
    $iMonth = $j + 2 - 12 * $i;
    $iYear = 4 * $k + $n + $i - 4716;

    $iYear  = strval( $iYear );
    $iMonth = strval( $iMonth );
    $iDay   = strval( $iDay );

    $iYear  = str_pad( $iYear,  4, '0', STR_PAD_LEFT );
    $iMonth = str_pad( $iMonth, 2, '0', STR_PAD_LEFT );
    $iDay   = str_pad( $iDay,   2, '0', STR_PAD_LEFT );

    $julian_date = $iYear . '-' . $iMonth . '-' . $iDay;

    return $julian_date;
  }
  #------------------------------------------------------------------------------------------------------

  function islamic_date_to_julian_date( $iYear, $iMonth, $iDay ) {

    $julian_day_number = $this->islamic_date_to_julian_day_number( $iYear, $iMonth, $iDay );
    return $this->julian_day_number_to_date( $julian_day_number );
  }
  #------------------------------------------------------------------------------------------------------
  #========================= End of conversion functions based on algorithms ============================
  #=========================       by Waleed Muhanna and Kees Couprie        ============================
  #------------------------------------------------------------------------------------------------------

  function enter_hijri_date_for_conversion() {

    html::form_start( 'islamic_date_entity', 'convert_hijri_date_to_julian' );
    html::span_start( 'class="highlight1"' );

    #----
    # Day
    #----
    $day = $this->read_post_parm( 'hijri_day' );
    if( $day == NULL ) $day = 0;
    html::dropdown_start( $fieldname = 'hijri_day', $label = 'Day' );
    for( $i = 1; $i <= 31; $i++ ) { # if we want a blank option, set loop to start from 0 instead of 1.
      $display = strval( $i );
      if( $display == '0' ) $display = '';
      html::dropdown_option( $internal_value = $i, $displayed_value = $display, $selection = $day );
    }
    html::dropdown_end();
    echo ' ';

    #------
    # Month
    #------
    html::span_start( 'class="narrowspaceonleft"' );
    $month = $this->read_post_parm( 'hijri_month' );
    if( $month == NULL ) $month = 0;
    html::dropdown_start( $fieldname = 'hijri_month', $label = 'Month' );
    for( $i = 1; $i <= 12; $i++ ) { # if we want a blank option, set loop to start from 0 instead of 1.
      $display = $this->month_list[ $i ];
      html::dropdown_option( $internal_value = $i, $displayed_value = $display, $selection = $month );
    }
    html::dropdown_end();
    echo ' ';
    html::span_end();

    #-----
    # Year
    #-----
    html::span_start( 'class="narrowspaceonleft"' );
    $year = $this->read_post_parm( 'hijri_year' );
    html::input_field( $fieldname = 'hijri_year', $label = 'Year', $value = $year, $in_table = FALSE, 
                       $size = 4, $tabindex=1, $label_parms = NULL, $data_parms = NULL, 
                       $input_parms = 'onchange="js_check_value_is_numeric( this )"' );
    html::span_end();  # narrow space on left
    html::span_end();  # highlighted


    html::span_start( 'class="narrowspaceonleft"' );
    html::submit_button( 'convert_button', 'Convert' );
    html::span_end();

    html::form_end();

    html::new_paragraph();
    html::italic_start();
    echo LINEBREAK;

    echo 'Date conversion algorithms are from ';
    html::link( 'http://members.casema.nl/couprie/calmath/index.html', 'Calendar Math by Kees Couprie',
                'Calendar Math by Kees Couprie', '_blank' );
    html::new_paragraph();
    echo 'Kees Couprie in turn acknowledges that certain of his algorithms are based on ';
    html::link( 'http://fisher.osu.edu/~muhanna_1/IslamicTimer.html', 'IslamicTimer by Waleed Muhanna',
                'IslamicTimer by Waleed Muhanna', '_blank' );
    html::italic_end();
    html::new_paragraph();
  }
  #------------------------------------------------------------------------------------------------------

  function convert_hijri_date_to_julian() {

    $year  = $this->read_post_parm( 'hijri_year' );
    $month = $this->read_post_parm( 'hijri_month' );
    $day   = $this->read_post_parm( 'hijri_day' );

    if( strlen( $year ) < 1 || strlen( $year ) > 4 ) {
      html::div_start( 'class="errmsg"' );
      echo 'Error: year must be entered as a figure between 1 and 9999.';
      html::div_end();
      html::new_paragraph();
      $this->enter_hijri_date_for_conversion();
      return;
    }

    $hijri_date = $year . '-' . $month . '-' . $day;  # for display
    $hijri_date_in_words = $day . ' ' . $this->get_month_name( $month ) . ' ' . $year;  # for display

    $julian_day_number = $this->islamic_date_to_julian_day_number( $year, $month, $day );

    $julian_date = $this->julian_day_number_to_date( $julian_day_number );

    $julian_date_in_words = $this->db_select_one_value( "select to_char( '$julian_date'::date, 'dd Mon yyyy' )" );

    $gregorian_date = $this->db_select_one_value( "select to_date( '$julian_day_number', 'J' )" );
    $gregorian_date_in_words = $this->db_select_one_value( "select to_char( '$gregorian_date'::date, 'dd Mon yyyy' )" );

    html::h4_start();
    echo 'Conversion results: ';
    html::h4_end();

    html::table_start( 'class="datatab widelyspacepadded"' );
    #--
    html::tablerow_start();

    html::tabledata( 'Hijri date' );
    html::tabledata( $hijri_date );
    html::tabledata( $hijri_date_in_words );

    html::tablerow_end();
    #--
    html::tablerow_start();

    html::tabledata( 'Julian equivalent', 'class="highlight1 bold"' );
    html::tabledata( $julian_date, 'class="highlight1 bold"' );
    html::tabledata( $julian_date_in_words, 'class="highlight1 bold"' );

    html::tablerow_end();
    #--
    html::tablerow_start();

    html::tabledata( 'Gregorian equivalent (if applicable)' );
    html::tabledata( $gregorian_date );
    html::tabledata( $gregorian_date_in_words );

    html::tablerow_end();
    #--
    html::table_end();

    html::new_paragraph();
    html::h4_start();
    echo 'Convert another date: ';
    html::h4_end();

    $this->enter_hijri_date_for_conversion();
  }
  #------------------------------------------------------------------------------------------------------

  function hijri_date_in_words( $year1, $month1, $day1, 
                                $year2 = NULL, $month2 = NULL, $day2 = NULL, $is_range = NULL ) {
    $date_in_words = '';

    $string1 = $year1;
    $string2 = $year2;

    if( $month1 ) {
      $string1 = trim( $this->get_month_name( $month1 ) . ' ' . $string1 );
      if( $day1 ) $string1 = $day1 . ' ' . $string1;
    }

    if( $month2 ) {
      $string2 = trim( $this->get_month_name( $month2 ) . ' ' . $string2 );
      if( $day2 ) $string2 = $day2 . ' ' . $string2;
    }

    if( $string1 && $string2 )
      $date_in_words = $string1 . ' to ' . $string2;

    elseif( $string2 )
      $date_in_words = $string2 . ' or before';

    elseif( $string1 ) {
      if( $is_range )
        $date_in_words = $string1 . ' or after';
      else
        $date_in_words = $string1;
    }

    return $date_in_words;
  }
  #------------------------------------------------------------------------------------------------------
  #----------------- Functions for use with people's dates: birth, death, flourished --------------------
  #------------------------------------------------------------------------------------------------------
  # We will need some extra fields for people's dates, because 'flourished' in particular cannot be
  # reduced to just the end of its range as we do when creating the 'sortable' version of date of work.
  #------------------------------------------------------------------------------------------------------

  function set_ids_for_css( $fieldname, $initialise = FALSE ) {

    parent::set_ids_for_css( $fieldname, $initialise );

    # Extra fields for conversion of Islamic dates of birth/death/flourishing into CE.
    $this->start_date_ce_id = $this->get_start_date_ce_fieldname( $this->first_fieldname );
    $this->end_date_ce_id = $this->get_end_date_ce_fieldname( $this->first_fieldname );

    $this->ce_span_id  = $this->get_span_id( $this->get_end_date_ce_fieldname( $this->first_fieldname ));

    switch( $fieldname ) {  # we now need to squeeze in some free-text date entry fields for people
      case 'date_of_birth':
      case 'date_of_death':
      case 'flourished':
        $this->date_in_words_id = $fieldname . '_in_orig_calendar';
        break;
          
      case 'date_of_birth2':
      case 'date_of_death2':
      case 'flourished2':
        $this->date_in_words_id = substr( $fieldname, 0, -1 ) . '_in_orig_calendar';
        break;

      default:
        $this->date_in_words_id = '';
    }
  }
  #------------------------------------------------------------------------------------------------------

  function get_start_date_ce_fieldname( $fieldname ) {
    return $fieldname . '_start_ce';
  }
  #----------------------------------------------------------------------------------

  function get_end_date_ce_fieldname( $fieldname ) {
    return $fieldname . '_end_ce';
  }
  #----------------------------------------------------------------------------------

  function extra_date_fields() { # a Date Entity method that can be overridden by child classes, 
                                 # in this case Islamic Date Entity
    html::new_paragraph();

    $this->start_date_ce_field();

    $this->end_date_ce_field();

    html::new_paragraph();
  }
  #----------------------------------------------------------------------------------

  function get_call_to_extra_onchange_script( $changed_fieldname ) { # a Date Entity method that can be overridden by
                                                                     # child classes, in this case Islamic Date Entity
    if( ! $this->convert_start_and_end_dates_to_ce ) return '';

    # The changed field should end in _year, _month or _day. Strip these off to get the core field.
    $endings = array( '_year', '_month', '_day' );
    $changed_root = '';
    foreach( $endings as $ending ) {
      if( $this->string_ends_with( $changed_fieldname, $ending ) ) {
        $changed_root = substr( $changed_fieldname, 0, 0 - strlen( $ending )); # e.g. 'flourished' or 'flourished2'
        break;
      }
    }

    if( ! $changed_root ) 
      return ''; # in practice this should never happen: changed field should always be year, month or day

    if( $this->string_ends_with( $changed_root, '2' ) ) {
      $ce_field = $this->get_end_date_ce_fieldname( substr( $changed_root, 0, -1 ) );
    }
    else {
      $ce_field = $this->get_start_date_ce_fieldname( $changed_root );
    }

    return ';' . " convert_hijri_to_ce( '$changed_root', '$ce_field' )";
  }
  #----------------------------------------------------------------------------------

  function get_call_to_extra_onclick_script( $checkbox_id ) { # a Date Entity method that can be overridden by
                                                              # child classes, in this case Islamic Date Entity
    if( ! $this->convert_start_and_end_dates_to_ce ) return '';

    return "show_or_hide_ce_end_date( '$this->range_checkbox_id', '$this->end_date_ce_id', '$this->ce_span_id' ); ";
  }
  #----------------------------------------------------------------------------------

  function start_date_ce_field() { # the Christian/CE version of 'Date from'

    $fieldname = $this->start_date_ce_id;
    $field_value = $this->$fieldname;

    html::label( 'CE equivalent:', $fieldname . '_label', 'for="' . $fieldname . '" class="bold"' );

    html::input_field( $fieldname, NULL, $field_value, FALSE, $size = STD_DATE_INPUT_FIELD_SIZE,
                       $tabindex, NULL, NULL, $input_parms = $parms );
  }
  #----------------------------------------------------------------------------------

  function end_date_ce_field() { # the Christian/CE version of 'Date to'

    $fieldname = $this->end_date_ce_id;
    $field_value = $this->$fieldname;

    html::span_start( 'id="' . $this->ce_span_id . '" class="narrowspaceonleft"' );

    html::label( 'to ', $fieldname . '_label', 'for="' . $fieldname . '"' );

    html::input_field( $fieldname, NULL, $field_value, FALSE, $size = STD_DATE_INPUT_FIELD_SIZE,
                       $tabindex, NULL, NULL, $input_parms = $parms );

    html::span_end();  # CE end date span

    if( $this->suppress_display ) {
      $script = "var the_span = document.getElementById( '$this->ce_span_id' );" . NEWLINE
              . "the_span.style.display = 'none';"                               . NEWLINE;
      html::write_javascript_function( $script );
    }

    html::new_paragraph();
    html::italic_start();
    echo 'CE date is auto-generated when Hijri date changes, but can also be manually entered.';
    echo LINEBREAK;
    echo 'If entering the CE date manually, please use';
    html::italic_end();

    html::bold_start();
    echo ' yyyy-mm-dd';
    html::bold_end();

    html::italic_start();
    echo ' format, with 01-01 standing for unknown month/day.';

    html::italic_end();
  }
  #----------------------------------------------------------------------------------

  function write_script_to_convert_start_and_end_dates_to_ce() {

    $script  = 'function convert_hijri_to_ce( changed_fieldname, target_fieldname ) { '        . NEWLINE;

    $script .= '  var year_fieldname  = changed_fieldname + "_year";'                          . NEWLINE;
    $script .= '  var month_fieldname = changed_fieldname + "_month";'                         . NEWLINE;
    $script .= '  var day_fieldname   = changed_fieldname + "_day";'                           . NEWLINE;

    $script .= '  var year_field  = document.getElementById( year_fieldname );'                . NEWLINE;
    $script .= '  var month_field = document.getElementById( month_fieldname );'               . NEWLINE;
    $script .= '  var day_field   = document.getElementById( day_fieldname );'                 . NEWLINE;

    $script .= '  var year_value  = year_field.value;'                                         . NEWLINE;
    $script .= '  var month_value = month_field.value;'                                        . NEWLINE;
    $script .= '  var day_value   = day_field.value;'                                          . NEWLINE;

    $script .= '  var target_field  = document.getElementById( target_fieldname );'            . NEWLINE;

    $script .= '  if( year_value == "" || isNaN( parseInt( year_value ))) {'                   . NEWLINE;
    $script .= '    year_value = parseInt( "9999" );'                                          . NEWLINE;
    $script .= '  } '                                                                          . NEWLINE;

    $script .= '  if( parseInt( year_value ) >= 9999 ) { // unknown year '                     . NEWLINE;
    $script .= '    target_field.value = "";'                                                  . NEWLINE;
    $script .= '    return;'                                                                   . NEWLINE;
    $script .= '  } '                                                                          . NEWLINE;

    $script .= '  var doing_end_date = false;'                                                 . NEWLINE;
    $script .= '  var last_char = changed_fieldname.substr( changed_fieldname.length - 1, 1 )' . NEWLINE;
    $script .= '  if( last_char == "2" ) { '                                                   . NEWLINE;
    $script .= '    doing_end_date = true;'                                                    . NEWLINE;
    $script .= '  } '                                                                          . NEWLINE;

    $script .= '  if( doing_end_date == true ) { '                                             . NEWLINE;
    $script .= '    if( month_value == 0 || isNaN( parseInt( month_value ))) {'                . NEWLINE;
    $script .= '      month_value = parseInt( "12" );'                                         . NEWLINE;
    $script .= '    } '                                                                        . NEWLINE;
    $script .= '    if( day_value == 0 || isNaN( parseInt( day_value ))) {'                    . NEWLINE;
    $script .= '      switch( month_value ) { '                                                . NEWLINE;
    $script .= '        case 9:'                                                               . NEWLINE;
    $script .= '        case 4:'                                                               . NEWLINE;
    $script .= '        case 6:'                                                               . NEWLINE;
    $script .= '        case 11:'                                                              . NEWLINE;
    $script .= '          day_value = parseInt( "30" );'                                       . NEWLINE;
    $script .= '          break;'                                                              . NEWLINE;
    $script .= '        case 2:'                                                               . NEWLINE;
    $script .= '          day_value = parseInt( "28" );'                                       . NEWLINE;
    $script .= '          break;'                                                              . NEWLINE;
    $script .= '        default:'                                                              . NEWLINE;
    $script .= '          day_value = parseInt( "31" );'                                       . NEWLINE;
    $script .= '      } '                                                                      . NEWLINE;
    $script .= '    } '                                                                        . NEWLINE;
    $script .= '  } '                                                                          . NEWLINE;

    $script .= '  else { // doing start date '                                                 . NEWLINE;
    $script .= '    if( month_value == 0 || isNaN( parseInt( month_value ))) {'                . NEWLINE;
    $script .= '      month_value = parseInt( "1" );'                                          . NEWLINE;
    $script .= '    } '                                                                        . NEWLINE;
    $script .= '    if( day_value == 0 || isNaN( parseInt( day_value ))) {'                    . NEWLINE;
    $script .= '      day_value = parseInt( "1" );'                                            . NEWLINE;
    $script .= '    } '                                                                        . NEWLINE;
    $script .= '  } '                                                                          . NEWLINE;

    $script .= '  var result = islToChr( year_value, month_value, day_value );'                . NEWLINE;
    $script .= '  target_field.value = result;'                                                . NEWLINE;

    $script .= '} ' . NEWLINE;

    html::write_javascript_function( $script );
  }
  #----------------------------------------------------------------------------------

  function write_script_to_show_or_hide_ce_end_date() {

    $script  = 'function show_or_hide_ce_end_date( checkbox_id, field_id, span_id ) { ' . NEWLINE;

    $script .= '  var the_checkbox = document.getElementById( checkbox_id );'           . NEWLINE;
    $script .= '  var the_field    = document.getElementById( field_id );'              . NEWLINE;
    $script .= '  var the_span     = document.getElementById( span_id );'               . NEWLINE;

    $script .= '  if( the_checkbox.checked == true ) { // date range '                  . NEWLINE;
    $script .= '    the_span.style.display = "inline";'                                 . NEWLINE;
    $script .= '  } '                                                                   . NEWLINE;

    $script .= '  else { // not a range '                                               . NEWLINE;
    $script .= '    the_field.value = "";'                                              . NEWLINE;
    $script .= '    the_span.style.display = "none";'                                   . NEWLINE;
    $script .= '  } '                                                                   . NEWLINE;

    $script .= '} '                                                                     . NEWLINE;

    html::write_javascript_function( $script );
  }
  #----------------------------------------------------------------------------------

  function get_label( $range_checkbox_ticked, $is_first_field ) {

    $label = parent::get_label( $range_checkbox_ticked, $is_first_field );

    if( trim( $label ) == 'Date:' && $this->convert_start_and_end_dates_to_ce ) 
      $label = 'Hijri date: ';

    return $label;
  }
  #----------------------------------------------------------------------------------

  function write_sortable_date_value_script() {  # overrides parent method - use 01-01 instead of 12-31

    $script_name = $this->generate_sortable_dates_script . '_value';

    $script  = 'function ' . $script_name . '( year_val, month_val, day_val ) {' . NEWLINE;
    $script .= '  var max_day_of_month;'                                         . NEWLINE;

    $script .= '  if( year_val == "" ) {'                                        . NEWLINE;
    $script .= '    year_val = "' . DTE_UNKNOWN_YEAR . '";'                      . NEWLINE;
    $script .= '  }'                                                             . NEWLINE;

    $script .= '  if( isNaN( parseInt( year_val ))) {'                           . NEWLINE;
    $script .= '    year_val = "' . DTE_UNKNOWN_YEAR . '";'                      . NEWLINE;
    $script .= '  }'                                                             . NEWLINE;

    $script .= '  month_val=parseInt( month_val );'                              . NEWLINE;
    $script .= '  day_val=parseInt( day_val );'                                  . NEWLINE;

    $script .= '  if( month_val == 0 ) {'                                        . NEWLINE;
    $script .= '    month_val = 1;'                      . NEWLINE;
    $script .= '  }'                                                             . NEWLINE;

    $script .= '  if( day_val == 0 ) {'   . NEWLINE;
    $script .= '    day_val = 1;'                                 . NEWLINE;
    $script .= '  }' . NEWLINE;

    $script .= '  if( day_val < 10 ) {'                                          . NEWLINE;
    $script .= '    day_val = "0" + day_val;'                                    . NEWLINE;
    $script .= '  }'                                                             . NEWLINE;

    $script .= '  if( month_val < 10 ) {'                                        . NEWLINE;
    $script .= '    month_val = "0" + month_val;'                                . NEWLINE;
    $script .= '  }'                                                             . NEWLINE;

    $script .= '  return year_val + "-" + month_val + "-" + day_val;'            . NEWLINE;

    $script .= '}'                                                               . NEWLINE;

    html::write_javascript_function( $script );

    return $script_name;
  }
  #----------------------------------------------------------------------------------

  function validate_parm( $parm_name ) {  # overrides parent method

    switch( $parm_name ) {

      case 'hijri_day':
      case 'hijri_month':
      case 'hijri_year':
        return $this->is_integer( $this->parm_value );

      default:
        return parent::validate_parm( $parm_name );
    }
  }
  #-----------------------------------------------------
}
?>
