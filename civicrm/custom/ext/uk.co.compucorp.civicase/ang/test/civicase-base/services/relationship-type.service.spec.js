(() => {
  describe('Relationship Type', () => {
    let RelationshipType, RelationshipTypeData;

    beforeEach(module('civicase-base', 'civicase.data'));

    beforeEach(inject((_RelationshipType_, _RelationshipTypeData_) => {
      RelationshipType = _RelationshipType_;
      RelationshipTypeData = _RelationshipTypeData_;
    }));

    describe('when getting all relationship types', () => {
      let returnedRelationshipType;

      beforeEach(() => {
        returnedRelationshipType = RelationshipType.getAll();
      });

      it('returns all the case statuses', () => {
        expect(returnedRelationshipType).toEqual(RelationshipTypeData.values);
      });
    });

    describe('when getting a relationship type by name', () => {
      let returnedRelationshipType, expectedRelationType;

      beforeEach(() => {
        expectedRelationType = {
          id: '17',
          name_a_b: 'Application Manager is',
          label_a_b: 'Application Manager is',
          name_b_a: 'Application Manager',
          label_b_a: 'Application Manager',
          description: 'Application Manager',
          is_active: '1'
        };
      });

      describe('when using a to b name ', () => {
        beforeEach(() => {
          returnedRelationshipType = RelationshipType.getByName('Application Manager is');
        });

        it('returns the related relationship type', () => {
          expect(returnedRelationshipType).toEqual(expectedRelationType);
        });
      });

      describe('when using b to a name ', () => {
        beforeEach(() => {
          returnedRelationshipType = RelationshipType.getByName('Application Manager');
        });

        it('returns the related relationship type', () => {
          expect(returnedRelationshipType).toEqual(expectedRelationType);
        });
      });
    });
  });
})();
