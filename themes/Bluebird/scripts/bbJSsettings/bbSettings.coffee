#
# Bluebird JS Hunter/Gatherer
# Standard Methods to Do Common Tasks
# Namespace: bbhg
# Created by: Dan Pozzie, NY Senate
# Last Update: 2013-06-13
# Language: Coffeescript
# Requires: jquery.1.7.2 via a "cj" jquery namespace
# Version: 0.0.1
#
#


class hg extends bb
  constructor: () ->
    # method, aryobj...
    # try
    #   throw "method doesn't exist" if !_packages[string.toLowerCase(method)]?
    #   _packages[method.toLowerCase()](aryobj)
    # catch e
    #   console.log e
  # public methods that bbhg adds to bbUtils
  data: (req) ->
    for method in req["methods"]
      _data[method] = _functions[method.toLowerCase()].apply()


_packages=
  constructor: () ->
    @request = new Request()
  getpackagesnames: (aryobj)->
    console.log Object.keys(@)
  



_functions=
  getLocations: (aryobj) ->
    # explicit declaration of locations
    # console.log aryobj
    # populate from php ideally

    # and then extend the two

    # populate
    locations =
      OPENLEG_BASE_URL: "http://open.nysenate.gov/legislation/search/"
    return locations
  getUserData: (aryobj) ->

  

class Request
  queueRequests: (req) ->
    # where you pull from php
    # which comes from packages
    # essentially you're going to run chained requests
    # go into say, getlocations, check the queue, if queue length > 0, continue making an object
    # to post to php, which will in turn, respond with it.
    # and then you'll wait for response, and then spit it back out the chain.
    # req
    #  req
    #   req
    #    req
    #      ajax(req).sync
    #      ajax(res)
    #    req(res) - pop object[req] = res
    #   req(res) - pop 
    #  req(res) - pop
    # return object
    # reason to do that? eliminate 30+ server calls. that's the point.

  getRequests: (req) ->
    # actual php pull

_data = {}


_settings=
  # requesting bool is assuming that 
  requesting: true 


window["bbhg"] = new hg
# # instantiation? this is tough
# if window.bbUtils?
#   console.log bbhg
#   # bbhg.class extends bbUtilsNS
#   # bbUtilsNS = bbhg
#   # window.bbUtils ?= new bbhg
# else
#   console.log "bbUtils needs to be declared first."
# http://open.nysenate.gov/legislation/search/?term=otype:bill+AND+oid:(S12*)&searchType=&format=json&pageSize=10
# $target_url = self::OPENLEG_BASE_URL.'/search/?term=otype:bill+AND+oid:('.$billNo.'+OR+'.$billNo.'*)&searchType=&format=json&pageSize=10';