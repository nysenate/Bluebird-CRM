((_) => {
  describe('formatCase', () => {
    let caseItem, customDataBlocks, formatCase;

    beforeEach(module('civicase', 'civicase.data'));

    beforeEach(inject((CasesData, _formatCase_) => {
      formatCase = _formatCase_;

      caseItem = _.extend({}, _.cloneDeep(CasesData.get().values[0]), {
        id: _.uniqueId(),
        case_type_id: '1',
        status_id: '1',
        'case_type_id.is_active': '0'
      });
    }));

    describe('custom data blocks', () => {
      describe('when there are inline custom data blocks', () => {
        beforeEach(() => {
          customDataBlocks = [
            generateCustomDataBlock({ style: 'Inline' }),
            generateCustomDataBlock({ style: 'Inline' }),
            generateCustomDataBlock({ style: 'Inline' })
          ];
          caseItem['api.CustomValue.getalltreevalues'] = {
            values: customDataBlocks
          };
        });

        it('stores the custom data blocks in a container for inline blocks', () => {
          expect(formatCase(caseItem).customData.Inline).toEqual([
            jasmine.objectContaining(customDataBlocks[0]),
            jasmine.objectContaining(customDataBlocks[1]),
            jasmine.objectContaining(customDataBlocks[2])
          ]);
        });
      });

      describe('when there are tab custom data blocks', () => {
        beforeEach(() => {
          customDataBlocks = [
            generateCustomDataBlock({ style: 'Inline' }),
            generateCustomDataBlock({ style: 'Tab' }),
            generateCustomDataBlock({ style: 'Inline' })
          ];
          caseItem['api.CustomValue.getalltreevalues'] = {
            values: customDataBlocks
          };
        });

        it('stores the custom data blocks in a container for tab blocks', () => {
          expect(formatCase(caseItem).customData.Tab).toEqual([
            jasmine.objectContaining(customDataBlocks[1])
          ]);
        });
      });

      describe('when the data blocks are sorted by weight', () => {
        beforeEach(() => {
          customDataBlocks = [
            generateCustomDataBlock({ style: 'Inline', weight: '1' }),
            generateCustomDataBlock({ style: 'Inline', weight: '2' }),
            generateCustomDataBlock({ style: 'Inline', weight: '3' })
          ];
          caseItem['api.CustomValue.getalltreevalues'] = {
            values: customDataBlocks
          };
        });

        it('converts the weights to number values so they can be properly sorted', () => {
          expect(formatCase(caseItem).customData.Inline).toEqual([
            jasmine.objectContaining({ weight: 1 }),
            jasmine.objectContaining({ weight: 2 }),
            jasmine.objectContaining({ weight: 3 })
          ]);
        });
      });
    });

    /**
     * @param {object} defaultValues default values to use when generating the
     *   custom data block.
     * @returns {object} a mock custom data block.
     */
    function generateCustomDataBlock (defaultValues) {
      const uniqueId = _.uniqueId();

      return _.extend({}, {
        id: uniqueId,
        name: `Custom_Data_Block_${uniqueId}`,
        title: `Custom Data Block ${uniqueId}`,
        style: 'Inline'
      }, defaultValues);
    }
  });
})(CRM._);
