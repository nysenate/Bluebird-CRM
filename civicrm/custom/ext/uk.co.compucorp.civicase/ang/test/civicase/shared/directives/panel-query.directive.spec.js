/* eslint-env jasmine */
(function ($, _) {
  describe('panelQuery', function () {
    var element, $compile, $q, $rootScope, $scope, crmApi, panelQueryScope, mockedResults;
    var NO_OF_RESULTS = 10;

    beforeEach(module('civicase.templates', 'civicase', 'crmUtil'));

    beforeEach(inject(function (_$compile_, _$q_, _$rootScope_, _crmApi_) {
      $compile = _$compile_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      crmApi = _crmApi_;

      $scope = $rootScope.$new();
      mockedResults = _.times(NO_OF_RESULTS, function () {
        return { id: _.random(1, 10) };
      });

      crmApi.and.returnValue($q.resolve({
        get: { values: mockedResults },
        count: NO_OF_RESULTS
      }));
    }));

    describe('[name] attribute', function () {
      beforeEach(function () {
        spyOn(_, 'uniqueId');
      });

      describe('when not provided', function () {
        var randomName;

        beforeEach(function () {
          randomName = 'panel-query-20';

          _.uniqueId.and.returnValue(randomName);
          compileDirective();
        });

        it('generates its own name', function () {
          expect(_.uniqueId).toHaveBeenCalledWith('panel-query-');
          expect(panelQueryScope.name).toBe(randomName);
        });
      });

      describe('when provided', function () {
        var givenName = 'foo-bar-baz';

        beforeEach(function () {
          $scope.panelName = givenName;
          compileDirective();
        });

        it('uses the given name', function () {
          expect(_.uniqueId).not.toHaveBeenCalled();
          expect(panelQueryScope.name).toBe(givenName);
        });
      });
    });

    describe('[query] attribute', function () {
      beforeEach(function () {
        $scope.queryData = { entity: 'Foo', params: { foo: 'foo' } };
        compileDirective();
      });

      it('store its value in its scope', function () {
        expect(panelQueryScope.query).toBeDefined();
        expect(panelQueryScope.query).toEqual($scope.queryData);
      });

      describe('one-way binding', function () {
        var originalSource;

        beforeEach(function () {
          originalSource = $scope.queryData;
          panelQueryScope.query = { baz: 'baz' };

          $scope.$digest();
        });

        it('has a one-way binding on the [query-data] value', function () {
          expect($scope.queryData).toEqual(originalSource);
        });
      });

      describe('`query` object', function () {
        describe('when it doesn\'t have the `entity` property', function () {
          beforeEach(function () {
            $scope.queryData = { params: {} };
          });

          it('sends an error message', function () {
            expect(compileDirective).toThrowError(/entity/);
          });
        });

        describe('when the `entity` property is an empty string', function () {
          beforeEach(function () {
            $scope.queryData = { entity: '', params: {} };
          });

          it('sends an error message', function () {
            expect(compileDirective).toThrowError(/entity/);
          });
        });
      });
    });

    describe('[handlers] attribute', function () {
      describe('results handler', function () {
        var resultsHandler;
        var newResults = ['foo', 'bar', 'baz'];

        beforeEach(function () {
          resultsHandler = jasmine.createSpy().and.callFake(function (results) {
            return newResults;
          });
          $scope.handlersData = { results: resultsHandler };

          compileDirective();
        });

        it('is called', function () {
          expect(resultsHandler).toHaveBeenCalled();
        });

        it('receives the full results list as an argument', function () {
          var arg = resultsHandler.calls.argsFor(0)[0];

          expect(arg.length).toBe(NO_OF_RESULTS);
          expect(arg.every(function (item) {
            return typeof item.id !== 'undefined';
          })).toBe(true);
        });

        it('allows to modify the list before it is stored', function () {
          expect(panelQueryScope.results).toBe(newResults);
        });
      });

      describe('range handler', function () {
        var rangeHandler;

        beforeEach(function () {
          rangeHandler = jasmine.createSpy().and.callFake(function (selectedRange) {});
          $scope.handlersData = { range: rangeHandler };

          compileDirective();
        });

        it('is called', function () {
          expect(rangeHandler).toHaveBeenCalled();
        });

        describe('when the selected range changes', function () {
          var newRange = 'month';

          beforeEach(function () {
            rangeHandler.calls.reset();

            panelQueryScope.selectedRange = newRange;
            panelQueryScope.$digest();
          });

          it('is called when the selected range changes', function () {
            expect(rangeHandler).toHaveBeenCalled();
          });

          it('receives the new selected range as an argument', function () {
            var arg = rangeHandler.calls.argsFor(0)[0];

            expect(arg).toBe(newRange);
          });

          it('receives the query params as an argument', function () {
            var arg = rangeHandler.calls.argsFor(0)[1];

            expect(arg).toEqual($scope.queryData.params);
          });
        });
      });
    });

    describe('[custom-data] attribute', function () {
      beforeEach(function () {
        $scope.customData = {
          customProp: 'foobarbaz',
          customFn: function () {}
        };

        compileDirective();
      });

      it('is stored in the isolated scope', function () {
        expect(_.isEmpty(panelQueryScope.customData)).toBe(false);
        expect(panelQueryScope.customData).toEqual(jasmine.objectContaining($scope.customData));
      });
    });

    describe('transclude slots', function () {
      it('requires the <panel-query-results> slot to be present', function () {
        expect(function () {
          compileDirective({});
        }).toThrow();
      });

      it('is optional to pass the following slots: <panel-query-actions>, <panel-query-title>, <panel-query-empty>', function () {
        expect(function () {
          compileDirective({ results: '<div></div>' });
        }).not.toThrow();
      });

      describe('scope compile', function () {
        beforeEach(function () {
          $scope.query = {
            entity: 'OuterEntity',
            params: { foo: 'outerFoo', bar: 'outerBar', baz: 'outerBaz' }
          };
          $scope.queryData = {
            entity: 'IsolatedEntity',
            params: { foo: 'isolatedFoo', bar: 'isolatedBar', baz: 'isolatedBaz' }
          };

          compileDirective({
            actions: '<div>{{query.entity}}</div>',
            empty: '<div>{{query.params.foo}}</div>',
            results: '<div>{{query.params.bar}}</div>',
            title: '<div>{{query.params.baz}}</div>'
          });
        });

        it('compiles the slot on its own isolated scope', function () {
          var actionsHtml = element.find('[ng-transclude="actions"]').html();
          var emptyHtml = element.find('[ng-transclude="empty"]').html();
          var resultsHtml = element.find('[ng-transclude="results"]').html();
          var titleHtml = element.find('[ng-transclude="title"]').html();

          expect(actionsHtml).toContain($scope.queryData.entity);
          expect(emptyHtml).toContain($scope.queryData.params.foo);
          expect(resultsHtml).toContain($scope.queryData.params.bar);
          expect(titleHtml).toContain($scope.queryData.params.baz);
        });
      });
    });

    describe('api requests', function () {
      var requests;

      beforeEach(function () {
        $scope.queryData = {
          entity: 'SomeEntity',
          params: { foo: 'foo', bar: 'bar' }
        };
        compileDirective();

        requests = crmApi.calls.argsFor(0)[0];
      });

      it('sends two api requests on init', function () {
        expect(crmApi).toHaveBeenCalled();
        expect(_.isObject(requests)).toEqual(true);
        expect(Object.keys(requests).length).toBe(2);
      });

      describe('first request', function () {
        var request;

        beforeEach(function () {
          request = requests[Object.keys(requests)[0]];
        });

        it('is for the given entity', function () {
          var entity = request[0];

          expect(entity).toBe($scope.queryData.entity);
        });

        describe('action', function () {
          describe('when none is specified', function () {
            it('is "get"', function () {
              var action = request[1];

              expect(action).toBe('get');
            });
          });

          describe('when it is specified', function () {
            var requests, request;

            beforeEach(function () {
              $scope.queryData.action = 'customaction';

              crmApi.calls.reset();
              compileDirective();

              requests = crmApi.calls.argsFor(0)[0];
              request = requests[Object.keys(requests)[0]];
            });

            it('is the given action', function () {
              var action = request[1];

              expect(action).toBe($scope.queryData.action);
            });
          });
        });

        describe('params', function () {
          var requestParams;

          beforeEach(function () {
            requestParams = request[2];
          });

          it('passes to the api the params in the `query` object', function () {
            expect(requestParams).toEqual(jasmine.objectContaining($scope.queryData.params));
          });

          it('automatically adds `sequential` to the params', function () {
            expect(requestParams).toEqual(jasmine.objectContaining({ sequential: 1 }));
          });

          describe('pagination', function () {
            it('adds the pagination params', function () {
              expect(requestParams.options).toBeDefined();
              expect(requestParams.options.limit).toBe(panelQueryScope.pagination.size);
              expect(requestParams.options.offset).toBeDefined(panelQueryScope.pagination.page + panelQueryScope.pagination.size);
            });

            describe('when the given params already have an `option` property', function () {
              var requests, request, requestParams;

              beforeEach(function () {
                $scope.queryData.params.options = {
                  limit: 10,
                  offset: 20,
                  sort: 'some_field ASC'
                };

                crmApi.calls.reset();
                compileDirective();

                requests = crmApi.calls.argsFor(0)[0];
                request = requests[Object.keys(requests)[0]];
                requestParams = request[2];
              });

              it('overrides the `limit` and `offset` property, enforcing its own', function () {
                expect(requestParams.options.limit).toBe(panelQueryScope.pagination.size);
                expect(requestParams.options.offset).toBeDefined(panelQueryScope.pagination.page + panelQueryScope.pagination.size);
              });

              it('leaves the other properties unchanged', function () {
                expect(requestParams.options.sort).toBeDefined();
                expect(requestParams.options.sort).toBe($scope.queryData.params.options.sort);
              });
            });
          });
        });

        describe('results', function () {
          it('stores the list of results', function () {
            expect(panelQueryScope.results).toEqual(mockedResults);
          });
        });
      });

      describe('second request', function () {
        var request;

        beforeEach(function () {
          request = requests[Object.keys(requests)[1]];
        });

        it('is for the given entity', function () {
          var entity = request[0];

          expect(entity).toBe($scope.queryData.entity);
        });

        it('is for getting the total count', function () {
          var action = request[1];

          expect(action).toBe('getcount');
        });

        it('passes to the api the params in the `query` object', function () {
          expect(request[2]).toEqual($scope.queryData.params);
        });

        it('stores the count', function () {
          expect(panelQueryScope.total).toEqual(NO_OF_RESULTS);
        });

        describe('when the count action is provided', function () {
          var action;

          beforeEach(function () {
            $scope.queryData.countAction = 'customcountaction';

            crmApi.calls.reset();
            compileDirective();

            requests = crmApi.calls.argsFor(0)[0];
            request = requests[Object.keys(requests)[1]];
            action = request[1];
          });

          it('gets the count using the provided count action', function () {
            expect(action).toBe($scope.queryData.countAction);
          });
        });
      });
    });

    describe('new api request triggers', function () {
      var getRequest, countRequest;

      beforeEach(function () {
        compileDirective();
        crmApi.calls.reset();
      });

      describe('when the query params change', function () {
        beforeEach(function () {
          panelQueryScope.pagination.page = 2;
          $scope.queryData.params.baz = 'baz';
          $scope.$digest();

          getRequest = crmApi.calls.argsFor(0)[0].get;
          countRequest = crmApi.calls.argsFor(0)[0].count;
        });

        it('triggers the api requests again', function () {
          expect(crmApi).toHaveBeenCalled();
        });

        it('passes the new params to the api', function () {
          expect(getRequest[2]).toEqual(jasmine.objectContaining({ baz: 'baz' }));
          expect(countRequest[2]).toEqual(jasmine.objectContaining({ baz: 'baz' }));
        });

        it('resets the pagination', function () {
          expect(panelQueryScope.pagination.page).toBe(1);
        });

        describe('cache', function () {
          beforeEach(function () {
            crmApi.calls.reset();

            panelQueryScope.pagination.page = 2;
            $scope.$digest();
          });

          it('clears the cache', function () {
            expect(crmApi).toHaveBeenCalled();
          });
        });
      });

      describe('when the current page changes', function () {
        beforeEach(function () {
          panelQueryScope.pagination.page = 2;
          panelQueryScope.$digest();

          getRequest = crmApi.calls.argsFor(0)[0].get;
          countRequest = crmApi.calls.argsFor(0)[0].count;
        });

        it('triggers an api request', function () {
          expect(crmApi).toHaveBeenCalled();
        });

        it('triggers the api request to fetch the data', function () {
          expect(getRequest).toBeDefined();
        });

        it('passes the new pagination offset to the request', function () {
          expect(getRequest[2].options.offset).toEqual(5);
        });

        it('does not trigger the api request to get the total count', function () {
          expect(countRequest).not.toBeDefined();
        });

        describe('when the page was not visited already', function () {
          beforeEach(function () {
            panelQueryScope.pagination.page = 2;
            panelQueryScope.$digest();
          });

          it('makes an api request', function () {
            expect(crmApi).toHaveBeenCalled();
          });
        });

        describe('when the page was already visited', function () {
          beforeEach(function () {
            panelQueryScope.pagination.page = 2;
            panelQueryScope.$digest();
            panelQueryScope.pagination.page = 3;
            panelQueryScope.$digest();

            crmApi.calls.reset();

            panelQueryScope.pagination.page = 2;
            panelQueryScope.$digest();
          });

          it('does not make an api request', function () {
            expect(crmApi).not.toHaveBeenCalled();
          });
        });
      });

      describe('when a watcher is triggered while already loading', function () {
        beforeEach(function () {
          panelQueryScope.loading.full = true;
          panelQueryScope.pagination.page = 2;
          $scope.$digest();
        });

        it('does not make an additional api call', function () {
          expect(crmApi.calls.count()).toBe(0);
        });
      });

      describe('when a watcher is triggered while already loading but force reload is necessary', function () {
        beforeEach(function () {
          panelQueryScope.loading.full = true;
          panelQueryScope.pagination.page = 2;
          panelQueryScope.config.forceReload = true;
          $scope.$digest();
        });

        it('reloads the panel data', function () {
          expect(crmApi.calls.count()).toBe(1);
        });

        it('resets the force reload flag', function () {
          expect(panelQueryScope.config.forceReload).toBe(false);
        });
      });
    });

    describe('pagination', function () {
      beforeEach(function () {
        compileDirective();
      });

      it('starts from page 1', function () {
        expect(panelQueryScope.pagination.page).toBe(1);
      });

      it('has a default page size of 5', function () {
        expect(panelQueryScope.pagination.size).toBe(5);
      });

      describe('range calculation', function () {
        describe('from', function () {
          it('calculated from: current page and page size', function () {
            expect(panelQueryScope.pagination.range.from).toBe(1);

            panelQueryScope.pagination.page = 3;
            panelQueryScope.$digest();

            expect(panelQueryScope.pagination.range.from).toBe(11);
          });
        });

        describe('to', function () {
          beforeEach(function () {
            panelQueryScope.total = 19;
          });

          it('calculated from: current page, page size, total count', function () {
            expect(panelQueryScope.pagination.range.to).toBe(5);

            panelQueryScope.pagination.page = 3;
            panelQueryScope.$digest();

            expect(panelQueryScope.pagination.range.to).toBe(15);

            panelQueryScope.pagination.page = 4;
            panelQueryScope.$digest();

            expect(panelQueryScope.pagination.range.to).toBe(19);
          });
        });
      });
    });

    describe('period range', function () {
      beforeEach(function () {
        compileDirective();
      });

      it('has a list of available ranges to select from', function () {
        expect(panelQueryScope.periodRange.map(function (range) {
          return range.value;
        })).toEqual(['week', 'month']);
      });

      it('has the week range selected by default', function () {
        expect(panelQueryScope.selectedRange).toBe('week');
      });
    });

    describe('reload event', function () {
      var panelName;

      beforeEach(function () {
        panelName = 'foo-bar';
        $scope.panelName = panelName;

        compileDirective();
        crmApi.calls.reset();
      });

      describe('when the event passes the panel name', function () {
        describe('when the name is standalone', function () {
          beforeEach(function () {
            $rootScope.$emit('civicase::PanelQuery::reload', panelName);
          });

          it('triggers the api requests again', function () {
            expect(crmApi).toHaveBeenCalled();
          });
        });

        describe('when the name is in a list', function () {
          beforeEach(function () {
            $rootScope.$emit('civicase::PanelQuery::reload', ['other-name', panelName]);
          });

          it('triggers the api requests again', function () {
            expect(crmApi).toHaveBeenCalled();
          });
        });
      });

      describe('when the event does not pass the panel name', function () {
        beforeEach(function () {
          $rootScope.$emit('civicase::PanelQuery::reload', 'other-name');
        });

        it('does not trigger the api requests again', function () {
          expect(crmApi).not.toHaveBeenCalled();
        });
      });
    });

    /**
     * Function responsible for setting up compilation of the directive
     *
     * @param {object} slots the transclude slots with their markup
     */
    function compileDirective (slots) {
      var attributes = 'query="queryData"';
      var content = '';
      var html = '<civicase-panel-query %{attributes}>%{content}</civicase-panel-query>';

      slots = slots || { results: '<div></div>' };

      $scope.config = {};
      $scope.queryData = $scope.queryData || {
        entity: 'FooBar', params: { foo: 'foo', bar: 'bar' }
      };

      attributes += $scope.panelName ? ' name="' + $scope.panelName + '"' : '';
      attributes += $scope.handlersData ? ' handlers="handlersData"' : '';
      attributes += $scope.customData ? ' custom-data="customData"' : '';
      attributes += ' config="config"';

      content += slots.actions ? '<panel-query-actions>' + slots.actions + '</panel-query-actions>' : '';
      content += slots.empty ? '<panel-query-empty>' + slots.empty + '</panel-query-empty>' : '';
      content += slots.results ? '<panel-query-results>' + slots.results + '</panel-query-results>' : '';
      content += slots.title ? '<panel-query-title>' + slots.title + '</panel-query-title>' : '';

      html = html.replace('%{attributes}', attributes);
      html = html.replace('%{content}', content);

      element = $compile(html)($scope);
      $scope.$digest();
      panelQueryScope = element.isolateScope();
    }
  });
}(CRM.$, CRM._));
