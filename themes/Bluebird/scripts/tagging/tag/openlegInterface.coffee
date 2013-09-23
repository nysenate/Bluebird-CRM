# create a js wrapper for making openleg calls

class OpenLeg
  constructor: () ->

  query: (args, @callback)->
    return false unless args.term? or args.term.length >= 3
    term = args.term
    @term = term
    year = args.year
    page = args.page || 1
    @page = ajaxStructure.data.pageIdx = page
    return @buildQuery(term, year, page)

  buildQuery:(term, year) ->
    # (child AND otype:bill) AND (year:2013 OR year:2011) AND (full:child OR full:children) NOT oid:A*
    # http://open.nysenate.gov/legislation/2.0/search.json?term=(child~%20OR%20child*)%20AND%20(otype:bill)%20AND%20(year:2013)%20AND%20(full:child~%20OR%20full:child*)%20NOT%20oid:A*
    fTerm = "(#{term}~ OR #{term}*)"
    fOType = "(otype:#{queryDefaults.otype})"
    fYear = "(year:#{@getCurrentSessionYear(year)})"
    fText = "(full:#{term}~ OR full:#{term}*)"
    fOid = "(oid:#{queryDefaults.oid})"
    validjsonpterm = bbUtils.spaceTo("underscore",term)
    # ajaxStructure.jsonpCallback = "bb_#{validjsonpterm}"
    ajaxStructure.data.term = "#{fTerm} AND #{fOType} AND #{fYear} AND #{fText} NOT #{fOid}"
    return @getQuery()

  getCurrentSessionYear: (year) ->
    if year? or isNaN(parseInt(year))
      dateobject = new Date()
      year = dateobject.getFullYear()
    year = parseInt(year) - 1 if year % 2 == 0
    return year

  getQuery: () ->
    get = cj.ajax(ajaxStructure)
    get.done((data) =>
      return @callback(@ripApartQueryData(data.response.metadata,data.response.results))
    )

  ripApartQueryData: (metadata,results) ->
    pagesLeft = Math.floor((metadata.totalresults-results.length)/ajaxStructure.data.pageSize)-ajaxStructure.data.pageIdx
    returnStructure=
      seeXmore: metadata.totalresults-results.length
      page: @page
      pagesLeft: pagesLeft
      term: @term
      results: []

    for result, index in results
      rs =
        noname: "#{result.oid} - (#{result.data.bill.sponsor.fullname})"
        forname: "#{result.oid} - for (#{result.data.bill.sponsor.fullname})"
        againstname: "#{result.oid} - against (#{result.data.bill.sponsor.fullname})"
        description: "#{result.data.bill.title}"
        url: "#{result.url}"
      returnStructure.results.push(rs)
    returnStructure

  queryDefaults=
    otype: 'bill'
    oid: 'A*'
    sort: 'modified'
    sortOrder: false

  ajaxStructure=
    url: 'http://open.nysenate.gov/legislation/2.0/search.jsonp'
    crossDomain: true
    dataType: "jsonp"
    cache: true
    data:
      term: ''
      pageSize: 10
      pageIdx: 1

      