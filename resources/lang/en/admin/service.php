<?php



return [

    /*
    |--------------------------------------------------------------------------
    | admin Service Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the admin sidebar links.
    | You are free to change them to anything you want to customize 
    | your views to better match your application.
    |
    */

    'services' => 'Services',
    'services_list' => 'Services List',
    'add_services' => 'Add Services',
    'title' => 'Title',
    'description' => 'Description',
    'price' => 'Price (Credit)',
    'price_info' => 'Currency :- ' . \Config::get('constants.CURRENCY_SYMBOL'),
    'duration' => 'Duration',
    'duration_info' => 'Duration allocated to each spot for this service',
    'select' => 'Select',
    'max_spot_limit' => 'Maximum Spot Limit',
    'max_spot_limit_info' => 'Maximum spot can book',
    'close_booking_before_time' => 'Close Booking Before Time',
    'close_booking_before_time_info' => 'Time to prevent booking for the spot. So if schedule spot time is 11:00 AM and if here select 1 hour then 10:00 AM is latest time to book that spot',
    'minutes' => 'Minutes',
    'hour' => 'Hour',
    'hours' => 'Hours',
    'service_type' => 'Service Type',
    'daily' => 'Daily',
    'weekly' => 'Weekly',
    'monthly' => 'Monthly',
    'yearly' => 'Yearly',
    'start_date' => 'Start Date',
    'start_date_info' => 'Service Schedule start from this date',
    'end_date' => 'End Date',
    'end_date_info' => 'Service Schedule end on this date',
    'start_time' => 'Start Time',
    'start_time_info' => 'Service Schedule start from this time with the interval of selected duration',
    'end_time' => 'End Time',
    'end_time_info' => 'Service Schedule end on this time',
    'sunday' => 'Sunday',
    'monday' => 'Monday',
    'tuesday' => 'Tuesday',
    'wednesday' => 'Wednesday',
    'thursday' => 'Thursday',
    'friday' => 'Friday',
    'saturday' => 'Saturday',
    'service_invalid_message' => 'Invalid service id',
    'service_add_message' => 'Service added successfully!',
    'service_update_message' => 'Service updated successfully!',
    'service_delete_message' => 'Service deleted successfully!',
    'service_status_message' => 'Status changed successfully!',
    'parent_category' => 'Category',
    'total_record' => 'Found records',
    'hourly_price' => 'Hourly Price',
    'fixed_price' => 'Fixed Price',
    'service_already_assigned' => 'Service already assigned to user',
    'service_assigned_successfully' => 'Service assigned successfully',
    'parent_category_info' => 'Belongs to which category',
    'add_service_frequency' => 'Add Service Frequency',
    'frequency_name' => 'Frequency Name',
    'frequency_days' => "Frequency Days",
    'service_frequency' => 'Add Service Frequency',
    'successfully_added_new_frequency' => 'Successfully added new frequency.',
    'service_frequency_message' => 'Service Frequency',
    'service_frequency_tooltip' => 'The frequency of repeat of the service',
    'service_frequency_price' => 'Service Frequency price',
    'set_price_for_service_frequency' => 'Add service price according to Frequency',
    'frequency_name' => 'Name',
    'service_frequency_name' => 'Service Frequency',
    'service_price' => 'Service Price',
    'additional_question' => 'Additional service',
    'service_option' => 'Option 1',
    'service_second_option' => 'Option 2',
    'option_1_price' => 'option 1 Price',
    'option_2_price' => 'option 2 Price',

];