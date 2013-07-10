Optaros TraceView
=================

The Optaros TraceView module provides AppNeta TraceView.

## Installation

Install all prerequisites for tracing requests with AppNeta TraceView. 
Install the code under app/ into your magento directory.

## Configuration

The TraceView module is configured through the app/etc/traceview.xml file.
Given that profiling is also available while serving the page through FPC, we're
not saving the config in the database but read it from the file instead.

Profiling is done through calls to Varien_Profiler which are used to send info
to TraceView. The layers configuration node provides a mapping between the 
Varien_Profiler timers and TraceView layers.
 
```xml
<?xml version="1.0"?>
<!-- Copyright (C) 2010 Optaros, Inc. All rights reserved. -->
<config>
  <global>
    <traceview>
 
      <!-- enable/disable (1/0) reporting to tracelytics -->
      <enabled>1</enabled>
 
      <!-- whether to use rum or not -->
      <use_rum>1</use_rum>
 
      <!-- timer <=> layer association -->
      <layers>
 
        <!--
          Timers that END in the given pattern are matched
          <PATTERN>layer</PATTERN>
        -->
 
        <!-- mage_init -->
        <system_config>mage_init</system_config>
        <config>mage_init</config>
        <load_cache>mage_init</load_cache>
        <stores>mage_init</stores>
        <init_front_controller>mage_init</init_front_controller>
        <apply_db_schema_updates>mage_init</apply_db_schema_updates>
 
        <!-- mage_url_rewrite -->
        <db_url_rewrite>mage_url_rewrite</db_url_rewrite>
        <config_url_rewrite>mage_url_rewrite</config_url_rewrite>
 
        <!-- mage_routers_match -->
        <routers_match>mage_routers_match</routers_match>
 
        <!-- mage_predispatch -->
        <pre-dispatch>mage_predispatch</pre-dispatch>
 
        <!-- mage_layout_load -->
        <layout_load>mage_layout_load</layout_load>
        <layout_generate_xml>mage_layout_load</layout_generate_xml>
        <layout_generate_blocks>mage_layout_load</layout_generate_blocks>
 
        <!-- mage_layout_render -->
        <layout_render>mage_layout_render</layout_render>
 
        <!-- mage_postdispatch -->
        <postdispatch>mage_postdispatch</postdispatch>
 
      </layers>
 
    </traceview>
  </global>
</config>
```

- `config/global/traceview/enabled` - enable/disable (1/0) reporting
- `config/global/traceview/use_rum` - enable/disable (1/0) generation of rum js code
- `config/global/traceview/layers` - timers to layers association


#### Layers

We can log different timers to different layers based on the layers  configuration node in traceview.xml.

```xml
<config>
    <global>
        <traceview>
            <layers>
                <ENDING_SEGMENT>LAYER_NAME</ENDING_SEGMENT>
            </layers>
        </traceview>
    </global>
</config>
```

The Varien_Profiler timer name is formatted as follows: 

    segment1 :: segment2 :: segment3 :: ... :: last segment

if the timer's last segment matches on of the ENDING_SEGMENT tags we have 
a match and we'll log that timer using that layer.

If no layer is found in the config file that matches the current timer, the 
timer will not be logged to tracelytics.

#### Profiling key stages

The default list of timer to layers mapping enables profiling of the key stages
of a Magento request: init, url-rewrite, pre-dispatch, load/render layout, 
post-dispatch

The **mage_init** layer covers config loading and cache, initializing front 
controllers and applying any DB schema updates.

**mage_url_rewrite** refers to any URL rewrites performed.

The **mage_layout_load** layer covers loading of all the layout updates.

**mage_layout_render** offers information about the time covered by rendering the layout.

**mage_predispatch** and **mage_postdispatch** cover action dispatching to the proper controllers while **mage_routers_match** gives information about finding the proper routers.

#### Controller/action logging

Zend controllers/actions are logged and available in the Top Controllers table
in the TraceView app view.

#### Real User Monitoring (RUM)

Real User Monitoring can be enabled through the `config/global/traceview/use_rum`
configuration node. Full Page Cache (FPC) available in Magento Enterprise is fully
supported.

#### Partitions

Traffic will be partitioned in TraceView based on customers.
Traffic from logged in customers will be partitioned into "LoggedIn" while
traffic from anonymous users will be partitioned into "Anonymous"
