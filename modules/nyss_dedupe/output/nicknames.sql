DROP TABLE IF EXISTS fn_group;
CREATE TABLE fn_group (
    id      int         PRIMARY KEY AUTO_INCREMENT,
    given   varchar(50) UNIQUE KEY,
    new     int(1)      DEFAULT '0'
);

DROP TABLE IF EXISTS fn_group_name;
CREATE TABLE fn_group_name (
    fn_group_id  int,
    name         varchar(50),
    INDEX (fn_group_id),
    INDEX (name),
    UNIQUE KEY (fn_group_id, name)
);

DROP TABLE IF EXISTS  fn_group_contact;
CREATE TABLE fn_group_contact (
    fn_group_id int,
    contact_id  int,
    INDEX (fn_group_id),
    INDEX (contact_id),
    UNIQUE KEY (fn_group_id, contact_id)
);
INSERT INTO fn_group (given, new) VALUES ('trenton',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'trent'), (@last_id,'trenton');

INSERT INTO fn_group (given, new) VALUES ('jason',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jace'), (@last_id,'jase'), (@last_id,'jason'), (@last_id,'jay');

INSERT INTO fn_group (given, new) VALUES ('augustine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'aggy'), (@last_id,'augie'), (@last_id,'august'), (@last_id,'augusta'), (@last_id,'augustina'), (@last_id,'augustine'), (@last_id,'augustus'), (@last_id,'austin'), (@last_id,'gus'), (@last_id,'guss'), (@last_id,'gussie'), (@last_id,'gussy'), (@last_id,'gustie'), (@last_id,'ina'), (@last_id,'tina');

INSERT INTO fn_group (given, new) VALUES ('unice',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'eunice'), (@last_id,'nicie'), (@last_id,'unice');

INSERT INTO fn_group (given, new) VALUES ('cynthia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cindy'), (@last_id,'cynthia'), (@last_id,'sina');

INSERT INTO fn_group (given, new) VALUES ('maxine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'max'), (@last_id,'maxine');

INSERT INTO fn_group (given, new) VALUES ('elisabeth',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ailie'), (@last_id,'alice'), (@last_id,'allie'), (@last_id,'ally'), (@last_id,'bess'), (@last_id,'bessie'), (@last_id,'bessy'), (@last_id,'beth'), (@last_id,'betsy'), (@last_id,'betty'), (@last_id,'elisabeth'), (@last_id,'elizabeth'), (@last_id,'elsie'), (@last_id,'lisa');

INSERT INTO fn_group (given, new) VALUES ('blanche',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bea'), (@last_id,'blanche');

INSERT INTO fn_group (given, new) VALUES ('arminta',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'arminta'), (@last_id,'minnie');

INSERT INTO fn_group (given, new) VALUES ('francesco',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'fran'), (@last_id,'francesco'), (@last_id,'frank'), (@last_id,'frankie');

INSERT INTO fn_group (given, new) VALUES ('cameron',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cam'), (@last_id,'cameron'), (@last_id,'ron'), (@last_id,'ronny');

INSERT INTO fn_group (given, new) VALUES ('francesca',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'fanny'), (@last_id,'fran'), (@last_id,'francesca'), (@last_id,'franny');

INSERT INTO fn_group (given, new) VALUES ('alonzo',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'al'), (@last_id,'alonzo'), (@last_id,'alphonzo'), (@last_id,'lon'), (@last_id,'lonas'), (@last_id,'lonnie'), (@last_id,'lonzo');

INSERT INTO fn_group (given, new) VALUES ('gabrielle',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'brie'), (@last_id,'eleanor'), (@last_id,'ella'), (@last_id,'elle'), (@last_id,'gabbie'), (@last_id,'gabby'), (@last_id,'gabe'), (@last_id,'gabriel'), (@last_id,'gabriella'), (@last_id,'gabrielle'), (@last_id,'garbrielle'), (@last_id,'helen'), (@last_id,'luella');

INSERT INTO fn_group (given, new) VALUES ('emily',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'emily');

INSERT INTO fn_group (given, new) VALUES ('winfred',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'winfred'), (@last_id,'winnie');

INSERT INTO fn_group (given, new) VALUES ('lavina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ina'), (@last_id,'lavina'), (@last_id,'lavinia'), (@last_id,'vina'), (@last_id,'viney'), (@last_id,'vonnie');

INSERT INTO fn_group (given, new) VALUES ('marissa',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'marissa'), (@last_id,'rissa');

INSERT INTO fn_group (given, new) VALUES ('immanuel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'emanuel'), (@last_id,'emmanuel'), (@last_id,'immanuel'), (@last_id,'manny'), (@last_id,'manuel');

INSERT INTO fn_group (given, new) VALUES ('ezekial',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ezekial'), (@last_id,'zeke');

INSERT INTO fn_group (given, new) VALUES ('charles',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'carl'), (@last_id,'charles'), (@last_id,'charley'), (@last_id,'charlie'), (@last_id,'chas'), (@last_id,'chaz'), (@last_id,'chic'), (@last_id,'chick'), (@last_id,'chuck'), (@last_id,'kori');

INSERT INTO fn_group (given, new) VALUES ('ferdinando',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ferdie'), (@last_id,'ferdinando'), (@last_id,'fred'), (@last_id,'nando');

INSERT INTO fn_group (given, new) VALUES ('aaron',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'aaron'), (@last_id,'erin'), (@last_id,'iron'), (@last_id,'ron'), (@last_id,'ronnie');

INSERT INTO fn_group (given, new) VALUES ('edward',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ed'), (@last_id,'eddie'), (@last_id,'eddy'), (@last_id,'edward'), (@last_id,'ned'), (@last_id,'neddie'), (@last_id,'neddy'), (@last_id,'ted'), (@last_id,'teddy');

INSERT INTO fn_group (given, new) VALUES ('danielle',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'aileen'), (@last_id,'ailie'), (@last_id,'allie'), (@last_id,'dan'), (@last_id,'dani'), (@last_id,'danielle'), (@last_id,'danni'), (@last_id,'eileen'), (@last_id,'elaine'), (@last_id,'eleanor'), (@last_id,'eleni'), (@last_id,'ella'), (@last_id,'elle'), (@last_id,'ellen'), (@last_id,'ellender'), (@last_id,'ellie'), (@last_id,'gabby'), (@last_id,'gabriella'), (@last_id,'helen'), (@last_id,'helene'), (@last_id,'lainie'), (@last_id,'lanna'), (@last_id,'lee'), (@last_id,'lena'), (@last_id,'lenora'), (@last_id,'lu'), (@last_id,'luella'), (@last_id,'lula'), (@last_id,'nell'), (@last_id,'nellie'), (@last_id,'nelly'), (@last_id,'nora'), (@last_id,'norah');

INSERT INTO fn_group (given, new) VALUES ('elias',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'eli'), (@last_id,'elias'), (@last_id,'lee');

INSERT INTO fn_group (given, new) VALUES ('gerardo',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'gera'), (@last_id,'gerardo');

INSERT INTO fn_group (given, new) VALUES ('alberta',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'alberta'), (@last_id,'alex'), (@last_id,'alla'), (@last_id,'allie'), (@last_id,'bert'), (@last_id,'berta'), (@last_id,'bertie'), (@last_id,'sandy');

INSERT INTO fn_group (given, new) VALUES ('evangeline',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ev'), (@last_id,'evan'), (@last_id,'evangeline'), (@last_id,'vangie');

INSERT INTO fn_group (given, new) VALUES ('eleazer',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'eleazer'), (@last_id,'lazar');

INSERT INTO fn_group (given, new) VALUES ('ambrose',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ambrose'), (@last_id,'brose');

INSERT INTO fn_group (given, new) VALUES ('emil',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'em'), (@last_id,'emil'), (@last_id,'emily'), (@last_id,'emma'), (@last_id,'emmie'), (@last_id,'emmy'), (@last_id,'erma'), (@last_id,'millie'), (@last_id,'milly');

INSERT INTO fn_group (given, new) VALUES ('nichole',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'nichole'), (@last_id,'nicki'), (@last_id,'nickie'), (@last_id,'nicky'), (@last_id,'nikki');

INSERT INTO fn_group (given, new) VALUES ('nichola',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'nichola'), (@last_id,'nicki'), (@last_id,'nickie'), (@last_id,'nicky'), (@last_id,'nikki');

INSERT INTO fn_group (given, new) VALUES ('prudence',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'prudence'), (@last_id,'prudy'), (@last_id,'prue');

INSERT INTO fn_group (given, new) VALUES ('frederic',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'fred'), (@last_id,'freddie'), (@last_id,'freddy'), (@last_id,'frederic');

INSERT INTO fn_group (given, new) VALUES ('isabella',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bel'), (@last_id,'bell'), (@last_id,'bella'), (@last_id,'belle'), (@last_id,'bess'), (@last_id,'bessie'), (@last_id,'bessy'), (@last_id,'beth'), (@last_id,'bethia'), (@last_id,'betsy'), (@last_id,'bette'), (@last_id,'betty'), (@last_id,'dicey'), (@last_id,'dicie'), (@last_id,'eba'), (@last_id,'ebba'), (@last_id,'elba'), (@last_id,'eli'), (@last_id,'elis'), (@last_id,'elise'), (@last_id,'eliza'), (@last_id,'elizabeth'), (@last_id,'ella'), (@last_id,'elle'), (@last_id,'elsa'), (@last_id,'elsie'), (@last_id,'ibby'), (@last_id,'isa'), (@last_id,'isabel'), (@last_id,'isabella'), (@last_id,'isabelle'), (@last_id,'ish'), (@last_id,'issy'), (@last_id,'izzy'), (@last_id,'lib'), (@last_id,'libby'), (@last_id,'liddy'), (@last_id,'lilibet'), (@last_id,'lily'), (@last_id,'lisa'), (@last_id,'lisbeth'), (@last_id,'lissie'), (@last_id,'liz'), (@last_id,'liza'), (@last_id,'lizabeth'), (@last_id,'lizbeth'), (@last_id,'lizzie'), (@last_id,'lizzy'), (@last_id,'sabe'), (@last_id,'sabra'), (@last_id,'sibella'), (@last_id,'tess'), (@last_id,'tibby');

INSERT INTO fn_group (given, new) VALUES ('wilfred',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'fred'), (@last_id,'wilfred'), (@last_id,'will'), (@last_id,'willie');

INSERT INTO fn_group (given, new) VALUES ('jacob',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jacob'), (@last_id,'jacobus'), (@last_id,'jake'), (@last_id,'jay');

INSERT INTO fn_group (given, new) VALUES ('elena',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'elena'), (@last_id,'helen');

INSERT INTO fn_group (given, new) VALUES ('isaiah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'isaiah'), (@last_id,'zadie'), (@last_id,'zay');

INSERT INTO fn_group (given, new) VALUES ('leonidas',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lee'), (@last_id,'leon'), (@last_id,'leonidas');

INSERT INTO fn_group (given, new) VALUES ('jebediah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dyer'), (@last_id,'jebediah'), (@last_id,'jed');

INSERT INTO fn_group (given, new) VALUES ('margarita',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'daisy'), (@last_id,'greta'), (@last_id,'gretta'), (@last_id,'madge'), (@last_id,'mag'), (@last_id,'maggie'), (@last_id,'maggy'), (@last_id,'maisie'), (@last_id,'marg'), (@last_id,'margaret'), (@last_id,'margaretta'), (@last_id,'margarita'), (@last_id,'marge'), (@last_id,'margery'), (@last_id,'margie'), (@last_id,'margo'), (@last_id,'margy'), (@last_id,'marjorie'), (@last_id,'marjory'), (@last_id,'meg'), (@last_id,'megan'), (@last_id,'meggy'), (@last_id,'meta'), (@last_id,'metta'), (@last_id,'midge'), (@last_id,'peg'), (@last_id,'peggie'), (@last_id,'peggy'), (@last_id,'rita');

INSERT INTO fn_group (given, new) VALUES ('henrietta',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'etta'), (@last_id,'etty'), (@last_id,'hal'), (@last_id,'hank'), (@last_id,'harry'), (@last_id,'hence'), (@last_id,'henny'), (@last_id,'henri'), (@last_id,'henrietta'), (@last_id,'henry'), (@last_id,'hetty'), (@last_id,'nettie'), (@last_id,'retta'), (@last_id,'yetta');

INSERT INTO fn_group (given, new) VALUES ('ephraim',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'eph'), (@last_id,'ephraim');

INSERT INTO fn_group (given, new) VALUES ('angus',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'angus'), (@last_id,'gus');

INSERT INTO fn_group (given, new) VALUES ('joel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'joe'), (@last_id,'joel'), (@last_id,'joey');

INSERT INTO fn_group (given, new) VALUES ('ryan',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ry'), (@last_id,'ryan');

INSERT INTO fn_group (given, new) VALUES ('herbert',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bert'), (@last_id,'herb'), (@last_id,'herbert'), (@last_id,'herbie');

INSERT INTO fn_group (given, new) VALUES ('delilah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'addy'), (@last_id,'adela'), (@last_id,'adelaide'), (@last_id,'adele'), (@last_id,'delilah'), (@last_id,'dell'), (@last_id,'della'), (@last_id,'heidi'), (@last_id,'lil'), (@last_id,'lila');

INSERT INTO fn_group (given, new) VALUES ('eldora',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dora'), (@last_id,'eldora');

INSERT INTO fn_group (given, new) VALUES ('harriet',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'hal'), (@last_id,'harold'), (@last_id,'harriet'), (@last_id,'harry'), (@last_id,'hattie'), (@last_id,'hatty'), (@last_id,'henry');

INSERT INTO fn_group (given, new) VALUES ('parthenia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'parthenia'), (@last_id,'phenie');

INSERT INTO fn_group (given, new) VALUES ('barbara',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bab'), (@last_id,'babbie'), (@last_id,'babs'), (@last_id,'barb'), (@last_id,'barbara'), (@last_id,'barbie'), (@last_id,'barby'), (@last_id,'benny'), (@last_id,'bobbie'), (@last_id,'bon'), (@last_id,'bonnie'), (@last_id,'bunnie');

INSERT INTO fn_group (given, new) VALUES ('vincenzo',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'vin'), (@last_id,'vincenzo'), (@last_id,'vinnie');

INSERT INTO fn_group (given, new) VALUES ('magdalena',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lena'), (@last_id,'magdalena'), (@last_id,'maggie');

INSERT INTO fn_group (given, new) VALUES ('ezra',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ezra'), (@last_id,'ezrie');

INSERT INTO fn_group (given, new) VALUES ('honora',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'honey'), (@last_id,'honora'), (@last_id,'nonie'), (@last_id,'nora'), (@last_id,'norah'), (@last_id,'norry');

INSERT INTO fn_group (given, new) VALUES ('dalton',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dal'), (@last_id,'dalton');

INSERT INTO fn_group (given, new) VALUES ('silvester',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'si'), (@last_id,'silvester'), (@last_id,'sly'), (@last_id,'syl'), (@last_id,'vester');

INSERT INTO fn_group (given, new) VALUES ('melvin',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mel'), (@last_id,'melvin');

INSERT INTO fn_group (given, new) VALUES ('elisa',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'elisa'), (@last_id,'lisa');

INSERT INTO fn_group (given, new) VALUES ('jeremiah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jem'), (@last_id,'jereme'), (@last_id,'jeremiah'), (@last_id,'jeremy'), (@last_id,'jerry'), (@last_id,'jez');

INSERT INTO fn_group (given, new) VALUES ('simeon',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sam'), (@last_id,'si'), (@last_id,'sim'), (@last_id,'simeon'), (@last_id,'simon'), (@last_id,'sion');

INSERT INTO fn_group (given, new) VALUES ('richard',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dick'), (@last_id,'dicken'), (@last_id,'dickie'), (@last_id,'dicky'), (@last_id,'rich'), (@last_id,'richard'), (@last_id,'richie'), (@last_id,'rick'), (@last_id,'ricky'), (@last_id,'ritchie');

INSERT INTO fn_group (given, new) VALUES ('jerome',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jerome'), (@last_id,'jerry');

INSERT INTO fn_group (given, new) VALUES ('cecily',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cecily'), (@last_id,'cissie'), (@last_id,'cissy'), (@last_id,'sisely');

INSERT INTO fn_group (given, new) VALUES ('nicholas',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'nic'), (@last_id,'nicholas'), (@last_id,'nick'), (@last_id,'nickie'), (@last_id,'nicky'), (@last_id,'nico');

INSERT INTO fn_group (given, new) VALUES ('deborah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'deb'), (@last_id,'debbie'), (@last_id,'debby'), (@last_id,'debi'), (@last_id,'debora'), (@last_id,'deborah');

INSERT INTO fn_group (given, new) VALUES ('phoebe',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'fifi'), (@last_id,'phoebe');

INSERT INTO fn_group (given, new) VALUES ('angelina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'angel'), (@last_id,'angelina'), (@last_id,'lina');

INSERT INTO fn_group (given, new) VALUES ('christian',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'chris'), (@last_id,'christian'), (@last_id,'christie'), (@last_id,'christy'), (@last_id,'kit'), (@last_id,'topher');

INSERT INTO fn_group (given, new) VALUES ('angeline',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'angel'), (@last_id,'angeline'), (@last_id,'lina');

INSERT INTO fn_group (given, new) VALUES ('micajah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cage'), (@last_id,'micajah');

INSERT INTO fn_group (given, new) VALUES ('lucille',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lou'), (@last_id,'lu'), (@last_id,'lucille'), (@last_id,'lucy');

INSERT INTO fn_group (given, new) VALUES ('eunice',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'eunice');

INSERT INTO fn_group (given, new) VALUES ('sanford',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sandy'), (@last_id,'sanford');

INSERT INTO fn_group (given, new) VALUES ('christa',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'chris'), (@last_id,'christa');

INSERT INTO fn_group (given, new) VALUES ('pernetta',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'nettie'), (@last_id,'pernetta');

INSERT INTO fn_group (given, new) VALUES ('webster',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'webb'), (@last_id,'webster');

INSERT INTO fn_group (given, new) VALUES ('luis',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'luie'), (@last_id,'luis');

INSERT INTO fn_group (given, new) VALUES ('sine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'hannah'), (@last_id,'jan'), (@last_id,'jane'), (@last_id,'janet'), (@last_id,'janie'), (@last_id,'jean'), (@last_id,'jeanie'), (@last_id,'jeanne'), (@last_id,'jeannie'), (@last_id,'jen'), (@last_id,'jennie'), (@last_id,'jenny'), (@last_id,'jess'), (@last_id,'jesse'), (@last_id,'jessie'), (@last_id,'jo'), (@last_id,'joan'), (@last_id,'joanna'), (@last_id,'jody'), (@last_id,'johannah'), (@last_id,'nona'), (@last_id,'nonie'), (@last_id,'sine');

INSERT INTO fn_group (given, new) VALUES ('barnabas',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'barnabas'), (@last_id,'barney');

INSERT INTO fn_group (given, new) VALUES ('bridgett',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'biddie'), (@last_id,'bridgett'), (@last_id,'bridie');

INSERT INTO fn_group (given, new) VALUES ('ariadne',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ariadne'), (@last_id,'arie');

INSERT INTO fn_group (given, new) VALUES ('mitchell',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ike'), (@last_id,'micah'), (@last_id,'michael'), (@last_id,'mick'), (@last_id,'mickey'), (@last_id,'micky'), (@last_id,'mike'), (@last_id,'mikey'), (@last_id,'miko'), (@last_id,'mitch'), (@last_id,'mitchell');

INSERT INTO fn_group (given, new) VALUES ('cicely',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cicely'), (@last_id,'cilla');

INSERT INTO fn_group (given, new) VALUES ('algernon',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'algernon'), (@last_id,'algy');

INSERT INTO fn_group (given, new) VALUES ('agatha',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'agatha'), (@last_id,'aggie'), (@last_id,'aggy');

INSERT INTO fn_group (given, new) VALUES ('franklin',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'fran'), (@last_id,'frank'), (@last_id,'franklin');

INSERT INTO fn_group (given, new) VALUES ('asahel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'asa'), (@last_id,'asahel');

INSERT INTO fn_group (given, new) VALUES ('janice',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jan'), (@last_id,'janice');

INSERT INTO fn_group (given, new) VALUES ('matilda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mat'), (@last_id,'matilda'), (@last_id,'mattie'), (@last_id,'matty'), (@last_id,'maud'), (@last_id,'pat'), (@last_id,'patty'), (@last_id,'tilda'), (@last_id,'tilla'), (@last_id,'tillie'), (@last_id,'tilly');

INSERT INTO fn_group (given, new) VALUES ('stephanie',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'aggie'), (@last_id,'aggy'), (@last_id,'agnes'), (@last_id,'ann'), (@last_id,'anna'), (@last_id,'anne'), (@last_id,'annette'), (@last_id,'annie'), (@last_id,'inez'), (@last_id,'nan'), (@last_id,'nancy'), (@last_id,'nanny'), (@last_id,'nessa'), (@last_id,'nessie'), (@last_id,'nina'), (@last_id,'steph'), (@last_id,'stephanie'), (@last_id,'stephen'), (@last_id,'stephie'), (@last_id,'steve'), (@last_id,'steven'), (@last_id,'stevie');

INSERT INTO fn_group (given, new) VALUES ('doris',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dora'), (@last_id,'doris');

INSERT INTO fn_group (given, new) VALUES ('joshua',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'joe'), (@last_id,'josh'), (@last_id,'joshua');

INSERT INTO fn_group (given, new) VALUES ('alexandria',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'alexa'), (@last_id,'alexandria');

INSERT INTO fn_group (given, new) VALUES ('carl',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'carl');

INSERT INTO fn_group (given, new) VALUES ('letitia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'letitia'), (@last_id,'lettice'), (@last_id,'lettie'), (@last_id,'tish'), (@last_id,'tisha'), (@last_id,'titia');

INSERT INTO fn_group (given, new) VALUES ('julia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jill'), (@last_id,'jule'), (@last_id,'jules'), (@last_id,'julia'), (@last_id,'julian'), (@last_id,'julie'), (@last_id,'juliet'), (@last_id,'julius');

INSERT INTO fn_group (given, new) VALUES ('kristine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'chris'), (@last_id,'christy'), (@last_id,'crissy'), (@last_id,'kissy'), (@last_id,'kris'), (@last_id,'krissy'), (@last_id,'kristine'), (@last_id,'kristy'), (@last_id,'tina');

INSERT INTO fn_group (given, new) VALUES ('mckenna',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ken'), (@last_id,'kenna'), (@last_id,'mckenna');

INSERT INTO fn_group (given, new) VALUES ('florence',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'flo'), (@last_id,'flora'), (@last_id,'florence'), (@last_id,'florrie'), (@last_id,'floss'), (@last_id,'flossie'), (@last_id,'flossy'), (@last_id,'floy');

INSERT INTO fn_group (given, new) VALUES ('clifford',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cliff'), (@last_id,'clifford'), (@last_id,'clifton'), (@last_id,'ford'), (@last_id,'tony');

INSERT INTO fn_group (given, new) VALUES ('socrates',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'crate'), (@last_id,'socrates');

INSERT INTO fn_group (given, new) VALUES ('jayme',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jay'), (@last_id,'jayme');

INSERT INTO fn_group (given, new) VALUES ('russell',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'russ'), (@last_id,'russell'), (@last_id,'rusty');

INSERT INTO fn_group (given, new) VALUES ('bernard',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'barney'), (@last_id,'bernard'), (@last_id,'berney'), (@last_id,'bernie'), (@last_id,'berny');

INSERT INTO fn_group (given, new) VALUES ('angela',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'angel'), (@last_id,'angela'), (@last_id,'angelica'), (@last_id,'angie');

INSERT INTO fn_group (given, new) VALUES ('dominico',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dom'), (@last_id,'dominico');

INSERT INTO fn_group (given, new) VALUES ('emeline',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'em'), (@last_id,'emeline'), (@last_id,'emily'), (@last_id,'emma'), (@last_id,'emmer'), (@last_id,'emmie'), (@last_id,'emmy'), (@last_id,'lina'), (@last_id,'millie');

INSERT INTO fn_group (given, new) VALUES ('tabitha',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'tabby'), (@last_id,'tabitha'), (@last_id,'tobitha');

INSERT INTO fn_group (given, new) VALUES ('abijah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ab'), (@last_id,'abijah');

INSERT INTO fn_group (given, new) VALUES ('hezekiah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'hezekiah'), (@last_id,'hy'), (@last_id,'ki'), (@last_id,'kiah'), (@last_id,'ky');

INSERT INTO fn_group (given, new) VALUES ('israel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'israel'), (@last_id,'ziggy');

INSERT INTO fn_group (given, new) VALUES ('ramona',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mona'), (@last_id,'ramona');

INSERT INTO fn_group (given, new) VALUES ('alastair',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'al'), (@last_id,'alastair');

INSERT INTO fn_group (given, new) VALUES ('odell',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'odell'), (@last_id,'odo');

INSERT INTO fn_group (given, new) VALUES ('katelyn',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'kate'), (@last_id,'katelyn'), (@last_id,'kay'), (@last_id,'kaye');

INSERT INTO fn_group (given, new) VALUES ('leonard',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lee'), (@last_id,'len'), (@last_id,'lenny'), (@last_id,'leo'), (@last_id,'leon'), (@last_id,'leona'), (@last_id,'leonard');

INSERT INTO fn_group (given, new) VALUES ('mathew',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mathew'), (@last_id,'matt');

INSERT INTO fn_group (given, new) VALUES ('floyd',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'floyd'), (@last_id,'lloyd');

INSERT INTO fn_group (given, new) VALUES ('amelia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'amelia'), (@last_id,'amy'), (@last_id,'emily'), (@last_id,'mel'), (@last_id,'melia'), (@last_id,'millie');

INSERT INTO fn_group (given, new) VALUES ('kendra',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'kay'), (@last_id,'kendra'), (@last_id,'kenji'), (@last_id,'kenny');

INSERT INTO fn_group (given, new) VALUES ('adolphus',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ad'), (@last_id,'ado'), (@last_id,'adolph'), (@last_id,'adolphus'), (@last_id,'dolph');

INSERT INTO fn_group (given, new) VALUES ('charity',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'charity'), (@last_id,'chat');

INSERT INTO fn_group (given, new) VALUES ('irvin',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'irvin'), (@last_id,'irving');

INSERT INTO fn_group (given, new) VALUES ('arabella',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ara'), (@last_id,'arabella'), (@last_id,'arabelle'), (@last_id,'arry'), (@last_id,'bel'), (@last_id,'bella'), (@last_id,'belle');

INSERT INTO fn_group (given, new) VALUES ('jedidiah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jed'), (@last_id,'jedidiah');

INSERT INTO fn_group (given, new) VALUES ('malinda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lindy'), (@last_id,'malinda');

INSERT INTO fn_group (given, new) VALUES ('salvador',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sal'), (@last_id,'salvador');

INSERT INTO fn_group (given, new) VALUES ('elysia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'elysia'), (@last_id,'lisa');

INSERT INTO fn_group (given, new) VALUES ('georgina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'georgie'), (@last_id,'georgina');

INSERT INTO fn_group (given, new) VALUES ('gabriel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'gabriel');

INSERT INTO fn_group (given, new) VALUES ('phillis',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'phillis'), (@last_id,'phyl');

INSERT INTO fn_group (given, new) VALUES ('georgine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'geordie'), (@last_id,'george'), (@last_id,'georgiana'), (@last_id,'georgie'), (@last_id,'georgine'), (@last_id,'jorge');

INSERT INTO fn_group (given, new) VALUES ('shelton',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'shel'), (@last_id,'shelly'), (@last_id,'shelton'), (@last_id,'tony');

INSERT INTO fn_group (given, new) VALUES ('henry',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'henry');

INSERT INTO fn_group (given, new) VALUES ('natasha',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'nat'), (@last_id,'natasha'), (@last_id,'tash'), (@last_id,'tasha'), (@last_id,'tashie');

INSERT INTO fn_group (given, new) VALUES ('kenneth',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ken'), (@last_id,'kenneth'), (@last_id,'kenny');

INSERT INTO fn_group (given, new) VALUES ('jacqueline',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jack'), (@last_id,'jackie'), (@last_id,'jacqueline');

INSERT INTO fn_group (given, new) VALUES ('theodosia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dosia'), (@last_id,'theo'), (@last_id,'theodosia');

INSERT INTO fn_group (given, new) VALUES ('india',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'india'), (@last_id,'indie'), (@last_id,'indy');

INSERT INTO fn_group (given, new) VALUES ('latisha',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'latisha'), (@last_id,'tish'), (@last_id,'tisha');

INSERT INTO fn_group (given, new) VALUES ('celina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'celina'), (@last_id,'lena');

INSERT INTO fn_group (given, new) VALUES ('abner',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ab'), (@last_id,'abbie'), (@last_id,'abby'), (@last_id,'abner');

INSERT INTO fn_group (given, new) VALUES ('iona',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'iona'), (@last_id,'onnie');

INSERT INTO fn_group (given, new) VALUES ('armilda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'armilda'), (@last_id,'milly');

INSERT INTO fn_group (given, new) VALUES ('beverley',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bev'), (@last_id,'beverley');

INSERT INTO fn_group (given, new) VALUES ('jehu',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'gee'), (@last_id,'hugh'), (@last_id,'hughie'), (@last_id,'jehu');

INSERT INTO fn_group (given, new) VALUES ('alverta',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'alverta'), (@last_id,'virdie');

INSERT INTO fn_group (given, new) VALUES ('joann',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jo'), (@last_id,'joann');

INSERT INTO fn_group (given, new) VALUES ('sidney',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sid'), (@last_id,'sidney'), (@last_id,'syd');

INSERT INTO fn_group (given, new) VALUES ('tilford',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'tilford'), (@last_id,'tillie');

INSERT INTO fn_group (given, new) VALUES ('camille',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'camille'), (@last_id,'cammie'), (@last_id,'cammy'), (@last_id,'millie');

INSERT INTO fn_group (given, new) VALUES ('brian',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'brian'), (@last_id,'bryan'), (@last_id,'bryant');

INSERT INTO fn_group (given, new) VALUES ('ferdinand',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ferdie'), (@last_id,'ferdinand'), (@last_id,'fred'), (@last_id,'freddie'), (@last_id,'freddy');

INSERT INTO fn_group (given, new) VALUES ('obadiah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dyer'), (@last_id,'obadiah'), (@last_id,'obe'), (@last_id,'obed'), (@last_id,'obie');

INSERT INTO fn_group (given, new) VALUES ('samuel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sam'), (@last_id,'sammy'), (@last_id,'samuel'), (@last_id,'sonny');

INSERT INTO fn_group (given, new) VALUES ('ignacio',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ignacio'), (@last_id,'nacho');

INSERT INTO fn_group (given, new) VALUES ('gerhardt',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'gay'), (@last_id,'gerhardt');

INSERT INTO fn_group (given, new) VALUES ('ronald',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'naldo'), (@last_id,'ro'), (@last_id,'ron'), (@last_id,'ronald'), (@last_id,'ronnie'), (@last_id,'ronny');

INSERT INTO fn_group (given, new) VALUES ('dominick',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dom'), (@last_id,'dominick'), (@last_id,'nick'), (@last_id,'nicky');

INSERT INTO fn_group (given, new) VALUES ('edyth',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'edie'), (@last_id,'edye'), (@last_id,'edyth');

INSERT INTO fn_group (given, new) VALUES ('tamsin',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'tam'), (@last_id,'tammy'), (@last_id,'tamsin');

INSERT INTO fn_group (given, new) VALUES ('adelphia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'addy'), (@last_id,'adele'), (@last_id,'adelphia'), (@last_id,'dell'), (@last_id,'delphia'), (@last_id,'philly');

INSERT INTO fn_group (given, new) VALUES ('leroy',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lee'), (@last_id,'leroy'), (@last_id,'roy');

INSERT INTO fn_group (given, new) VALUES ('rudolphus',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dolph'), (@last_id,'rolf'), (@last_id,'rollo'), (@last_id,'rudolph'), (@last_id,'rudolphus'), (@last_id,'rudy');

INSERT INTO fn_group (given, new) VALUES ('irwin',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'erwin'), (@last_id,'irwin');

INSERT INTO fn_group (given, new) VALUES ('obediah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dyer'), (@last_id,'obed'), (@last_id,'obediah'), (@last_id,'obie');

INSERT INTO fn_group (given, new) VALUES ('dwight',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dwight'), (@last_id,'ike');

INSERT INTO fn_group (given, new) VALUES ('martine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'martine'), (@last_id,'tine');

INSERT INTO fn_group (given, new) VALUES ('octavia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'octavia'), (@last_id,'tavia');

INSERT INTO fn_group (given, new) VALUES ('permelia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mellie'), (@last_id,'melly'), (@last_id,'milly'), (@last_id,'permelia');

INSERT INTO fn_group (given, new) VALUES ('elspeth',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'elsie'), (@last_id,'elspeth');

INSERT INTO fn_group (given, new) VALUES ('martina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'martina'), (@last_id,'tina');

INSERT INTO fn_group (given, new) VALUES ('evaline',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ev'), (@last_id,'eva'), (@last_id,'evaline'), (@last_id,'eve'), (@last_id,'evelina'), (@last_id,'eveline'), (@last_id,'evelyn'), (@last_id,'lena');

INSERT INTO fn_group (given, new) VALUES ('clarissa',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cissy'), (@last_id,'claire'), (@last_id,'clara'), (@last_id,'clare'), (@last_id,'clarice'), (@last_id,'clarissa');

INSERT INTO fn_group (given, new) VALUES ('josetta',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jettie'), (@last_id,'josetta');

INSERT INTO fn_group (given, new) VALUES ('marguerite',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'marguerite'), (@last_id,'peggy');

INSERT INTO fn_group (given, new) VALUES ('leonore',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'elenor'), (@last_id,'honor'), (@last_id,'leonore'), (@last_id,'nora');

INSERT INTO fn_group (given, new) VALUES ('josephine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'fina'), (@last_id,'jo'), (@last_id,'jody'), (@last_id,'joey'), (@last_id,'josephine'), (@last_id,'josey'), (@last_id,'josie'), (@last_id,'josy'), (@last_id,'jozy');

INSERT INTO fn_group (given, new) VALUES ('peregrine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'peregrine'), (@last_id,'perry');

INSERT INTO fn_group (given, new) VALUES ('susannah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'agnes'), (@last_id,'ann'), (@last_id,'anna'), (@last_id,'annette'), (@last_id,'annie'), (@last_id,'hannah'), (@last_id,'hattie'), (@last_id,'nan'), (@last_id,'nana'), (@last_id,'nance'), (@last_id,'nancy'), (@last_id,'nannie'), (@last_id,'nanny'), (@last_id,'nettie'), (@last_id,'nina'), (@last_id,'sue'), (@last_id,'sukey'), (@last_id,'suki'), (@last_id,'susan'), (@last_id,'susanna'), (@last_id,'susannah'), (@last_id,'susie'), (@last_id,'susy'), (@last_id,'suz'), (@last_id,'suzanne'), (@last_id,'suzy');

INSERT INTO fn_group (given, new) VALUES ('emmanuel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'emmanuel');

INSERT INTO fn_group (given, new) VALUES ('helena',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'aileen'), (@last_id,'ailie'), (@last_id,'eileen'), (@last_id,'elaine'), (@last_id,'eleanor'), (@last_id,'ellen'), (@last_id,'helen'), (@last_id,'helena'), (@last_id,'lena'), (@last_id,'nell'), (@last_id,'nellie');

INSERT INTO fn_group (given, new) VALUES ('oswald',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'oswald'), (@last_id,'ozzy'), (@last_id,'waldo');

INSERT INTO fn_group (given, new) VALUES ('ebenezer',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'eb'), (@last_id,'ebbie'), (@last_id,'eben'), (@last_id,'ebenezer'), (@last_id,'eber');

INSERT INTO fn_group (given, new) VALUES ('zachariah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'zach'), (@last_id,'zachariah'), (@last_id,'zacharias'), (@last_id,'zachary'), (@last_id,'zak'), (@last_id,'zeke');

INSERT INTO fn_group (given, new) VALUES ('tisha',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'tisha');

INSERT INTO fn_group (given, new) VALUES ('leanne',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'annie'), (@last_id,'lea'), (@last_id,'leanne');

INSERT INTO fn_group (given, new) VALUES ('felicia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'felicia'), (@last_id,'felix');

INSERT INTO fn_group (given, new) VALUES ('bridget',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'biddie'), (@last_id,'biddy'), (@last_id,'bridey'), (@last_id,'bridget'), (@last_id,'bridie'), (@last_id,'brie'), (@last_id,'cordelia'), (@last_id,'cordy'), (@last_id,'delia'), (@last_id,'fidelia');

INSERT INTO fn_group (given, new) VALUES ('uriah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'riah'), (@last_id,'uriah');

INSERT INTO fn_group (given, new) VALUES ('laveda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'laveda'), (@last_id,'veda');

INSERT INTO fn_group (given, new) VALUES ('ricardo',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ricardo'), (@last_id,'rick');

INSERT INTO fn_group (given, new) VALUES ('octavus',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'octavus'), (@last_id,'tavy');

INSERT INTO fn_group (given, new) VALUES ('edmund',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ed'), (@last_id,'eddie'), (@last_id,'eddy'), (@last_id,'edmund'), (@last_id,'ned'), (@last_id,'neddie'), (@last_id,'neddy'), (@last_id,'ted'), (@last_id,'teddy');

INSERT INTO fn_group (given, new) VALUES ('stephen',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'stephen');

INSERT INTO fn_group (given, new) VALUES ('violetta',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lettie'), (@last_id,'violetta');

INSERT INTO fn_group (given, new) VALUES ('eugene',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'eugene'), (@last_id,'eugenia'), (@last_id,'gen'), (@last_id,'gene'), (@last_id,'genie'), (@last_id,'jenny');

INSERT INTO fn_group (given, new) VALUES ('rachel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'rachel'), (@last_id,'rae'), (@last_id,'ray'), (@last_id,'shelly');

INSERT INTO fn_group (given, new) VALUES ('curtis',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'curt'), (@last_id,'curtis');

INSERT INTO fn_group (given, new) VALUES ('sybill',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sibbie'), (@last_id,'sybill');

INSERT INTO fn_group (given, new) VALUES ('edmond',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ed'), (@last_id,'eddie'), (@last_id,'eddy'), (@last_id,'edmond');

INSERT INTO fn_group (given, new) VALUES ('rosalind',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'rosalind'), (@last_id,'rosie');

INSERT INTO fn_group (given, new) VALUES ('hamilton',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ham'), (@last_id,'hamilton');

INSERT INTO fn_group (given, new) VALUES ('dickson',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dick'), (@last_id,'dickson');

INSERT INTO fn_group (given, new) VALUES ('dotha',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dotha'), (@last_id,'dotty');

INSERT INTO fn_group (given, new) VALUES ('terence',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'terence'), (@last_id,'terry');

INSERT INTO fn_group (given, new) VALUES ('marcus',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'marcellus'), (@last_id,'marcia'), (@last_id,'marcus'), (@last_id,'mark');

INSERT INTO fn_group (given, new) VALUES ('broderick',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'brady'), (@last_id,'broderick'), (@last_id,'brody'), (@last_id,'ricky'), (@last_id,'rod');

INSERT INTO fn_group (given, new) VALUES ('orange',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ora'), (@last_id,'orange');

INSERT INTO fn_group (given, new) VALUES ('lillian',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lil'), (@last_id,'lila'), (@last_id,'lillian'), (@last_id,'lilly'), (@last_id,'lily'), (@last_id,'lolly'), (@last_id,'odie');

INSERT INTO fn_group (given, new) VALUES ('james',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jacobus'), (@last_id,'jamer'), (@last_id,'james'), (@last_id,'jamie'), (@last_id,'jeb'), (@last_id,'jem'), (@last_id,'jemmy'), (@last_id,'jim'), (@last_id,'jimbo'), (@last_id,'jimmie'), (@last_id,'jimmy');

INSERT INTO fn_group (given, new) VALUES ('alexis',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'alexis'), (@last_id,'lexi');

INSERT INTO fn_group (given, new) VALUES ('melchizedek',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dick'), (@last_id,'melchizedek');

INSERT INTO fn_group (given, new) VALUES ('anastasia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ana'), (@last_id,'anastasia'), (@last_id,'stacy');

INSERT INTO fn_group (given, new) VALUES ('eva',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'eva');

INSERT INTO fn_group (given, new) VALUES ('jasper',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'casper'), (@last_id,'jap'), (@last_id,'jasper');

INSERT INTO fn_group (given, new) VALUES ('domenic',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dom'), (@last_id,'domenic');

INSERT INTO fn_group (given, new) VALUES ('diana',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'di'), (@last_id,'diana'), (@last_id,'dicey'), (@last_id,'didi');

INSERT INTO fn_group (given, new) VALUES ('tiffany',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'tiff'), (@last_id,'tiffany'), (@last_id,'tiffy');

INSERT INTO fn_group (given, new) VALUES ('steven',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'steven');

INSERT INTO fn_group (given, new) VALUES ('diane',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'di'), (@last_id,'diane'), (@last_id,'dicey'), (@last_id,'didi');

INSERT INTO fn_group (given, new) VALUES ('allan',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'al'), (@last_id,'alan'), (@last_id,'allan');

INSERT INTO fn_group (given, new) VALUES ('zebedee',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'zeb'), (@last_id,'zebedee');

INSERT INTO fn_group (given, new) VALUES ('alanson',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'al'), (@last_id,'alan'), (@last_id,'alanson'), (@last_id,'lanson');

INSERT INTO fn_group (given, new) VALUES ('elwood',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'elly'), (@last_id,'elwood'), (@last_id,'woody');

INSERT INTO fn_group (given, new) VALUES ('katrina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'kat'), (@last_id,'katrina'), (@last_id,'trina');

INSERT INTO fn_group (given, new) VALUES ('kingsley',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'king'), (@last_id,'kingsley');

INSERT INTO fn_group (given, new) VALUES ('myrtle',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mert'), (@last_id,'myrt'), (@last_id,'myrtle');

INSERT INTO fn_group (given, new) VALUES ('randolph',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dolph'), (@last_id,'rafe'), (@last_id,'randall'), (@last_id,'randolph'), (@last_id,'randy');

INSERT INTO fn_group (given, new) VALUES ('shannon',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'shanie'), (@last_id,'shannon');

INSERT INTO fn_group (given, new) VALUES ('arielle',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'arie'), (@last_id,'arielle');

INSERT INTO fn_group (given, new) VALUES ('jackson',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jack'), (@last_id,'jackson');

INSERT INTO fn_group (given, new) VALUES ('ashley',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ash'), (@last_id,'ashley');

INSERT INTO fn_group (given, new) VALUES ('douglas',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'doug'), (@last_id,'douglas');

INSERT INTO fn_group (given, new) VALUES ('daniel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dan'), (@last_id,'daniel'), (@last_id,'danny');

INSERT INTO fn_group (given, new) VALUES ('bethena',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'beth'), (@last_id,'bethena');

INSERT INTO fn_group (given, new) VALUES ('alison',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ailie'), (@last_id,'ali'), (@last_id,'alison'), (@last_id,'elsie');

INSERT INTO fn_group (given, new) VALUES ('vinson',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'vin'), (@last_id,'vince'), (@last_id,'vinnie'), (@last_id,'vinson');

INSERT INTO fn_group (given, new) VALUES ('judith',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'juda'), (@last_id,'jude'), (@last_id,'judi'), (@last_id,'judie'), (@last_id,'judith'), (@last_id,'judy');

INSERT INTO fn_group (given, new) VALUES ('benjamin',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ben'), (@last_id,'benjamin'), (@last_id,'benji'), (@last_id,'benjie'), (@last_id,'benjy'), (@last_id,'bennie'), (@last_id,'benny'), (@last_id,'jamie');

INSERT INTO fn_group (given, new) VALUES ('harold',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'harold');

INSERT INTO fn_group (given, new) VALUES ('arizona',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'arizona'), (@last_id,'ona'), (@last_id,'onie');

INSERT INTO fn_group (given, new) VALUES ('clarence',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'clair'), (@last_id,'clare'), (@last_id,'clarence'), (@last_id,'clay');

INSERT INTO fn_group (given, new) VALUES ('tasha',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'tasha');

INSERT INTO fn_group (given, new) VALUES ('hiram',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'hiram'), (@last_id,'hy');

INSERT INTO fn_group (given, new) VALUES ('pleasant',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'pleasant'), (@last_id,'ples');

INSERT INTO fn_group (given, new) VALUES ('kayla',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'kay'), (@last_id,'kayla');

INSERT INTO fn_group (given, new) VALUES ('delphine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'del'), (@last_id,'delphine');

INSERT INTO fn_group (given, new) VALUES ('california',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'california'), (@last_id,'callie');

INSERT INTO fn_group (given, new) VALUES ('euphemia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'effie'), (@last_id,'euphemia');

INSERT INTO fn_group (given, new) VALUES ('philip',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'filip'), (@last_id,'phil'), (@last_id,'philip'), (@last_id,'philippa'), (@last_id,'philly'), (@last_id,'phyl'), (@last_id,'phyllis'), (@last_id,'pip'), (@last_id,'pippa');

INSERT INTO fn_group (given, new) VALUES ('vandalia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'vandalia'), (@last_id,'vannie');

INSERT INTO fn_group (given, new) VALUES ('bartholomew',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bart'), (@last_id,'bartel'), (@last_id,'barth'), (@last_id,'bartholomew'), (@last_id,'bat');

INSERT INTO fn_group (given, new) VALUES ('renee',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'rae'), (@last_id,'renee');

INSERT INTO fn_group (given, new) VALUES ('montague',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'montague'), (@last_id,'monty');

INSERT INTO fn_group (given, new) VALUES ('isabeau',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bel'), (@last_id,'bella'), (@last_id,'isa'), (@last_id,'isabeau'), (@last_id,'sibella');

INSERT INTO fn_group (given, new) VALUES ('dorinda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dolly'), (@last_id,'dora'), (@last_id,'dorinda'), (@last_id,'dorothea');

INSERT INTO fn_group (given, new) VALUES ('sylvanus',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sly'), (@last_id,'syl'), (@last_id,'sylvanus');

INSERT INTO fn_group (given, new) VALUES ('estella',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'essie'), (@last_id,'essy'), (@last_id,'estella'), (@last_id,'estelle'), (@last_id,'stella');

INSERT INTO fn_group (given, new) VALUES ('sylvester',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'si'), (@last_id,'sly'), (@last_id,'sy'), (@last_id,'syl'), (@last_id,'sylvester'), (@last_id,'vessie'), (@last_id,'vester');

INSERT INTO fn_group (given, new) VALUES ('nowell',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'noel'), (@last_id,'nowell');

INSERT INTO fn_group (given, new) VALUES ('horace',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'harry'), (@last_id,'horace'), (@last_id,'horatio');

INSERT INTO fn_group (given, new) VALUES ('david',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dav'), (@last_id,'dave'), (@last_id,'davey'), (@last_id,'david'), (@last_id,'davie'), (@last_id,'davy'), (@last_id,'day'), (@last_id,'vida');

INSERT INTO fn_group (given, new) VALUES ('sondra',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dre'), (@last_id,'sondra'), (@last_id,'sonnie');

INSERT INTO fn_group (given, new) VALUES ('archibald',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'archibald'), (@last_id,'archie'), (@last_id,'archy'), (@last_id,'baldo');

INSERT INTO fn_group (given, new) VALUES ('lavinia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lavinia');

INSERT INTO fn_group (given, new) VALUES ('miriam',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'daisie'), (@last_id,'mae'), (@last_id,'maisie'), (@last_id,'mamie'), (@last_id,'maria'), (@last_id,'mariah'), (@last_id,'marie'), (@last_id,'mary'), (@last_id,'may'), (@last_id,'mia'), (@last_id,'mimi'), (@last_id,'minnie'), (@last_id,'miriam'), (@last_id,'mitzi'), (@last_id,'moll'), (@last_id,'molly'), (@last_id,'pol'), (@last_id,'polly'), (@last_id,'sukey');

INSERT INTO fn_group (given, new) VALUES ('loretta',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'etta'), (@last_id,'laura'), (@last_id,'loretta'), (@last_id,'lorie'), (@last_id,'lorrie'), (@last_id,'lottie'), (@last_id,'retta');

INSERT INTO fn_group (given, new) VALUES ('claudia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'caya'), (@last_id,'claud'), (@last_id,'claudia');

INSERT INTO fn_group (given, new) VALUES ('cinderella',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'arilla'), (@last_id,'cinderella'), (@last_id,'cindy'), (@last_id,'ella'), (@last_id,'rella'), (@last_id,'rilla');

INSERT INTO fn_group (given, new) VALUES ('leonora',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'elenor'), (@last_id,'ella'), (@last_id,'ellen'), (@last_id,'honor'), (@last_id,'leonora'), (@last_id,'nell'), (@last_id,'nellie'), (@last_id,'nora'), (@last_id,'norah');

INSERT INTO fn_group (given, new) VALUES ('valentina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'felty'), (@last_id,'val'), (@last_id,'valentina'), (@last_id,'vallie');

INSERT INTO fn_group (given, new) VALUES ('valentine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'felty'), (@last_id,'val'), (@last_id,'valentine'), (@last_id,'vallie');

INSERT INTO fn_group (given, new) VALUES ('gustavus',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'gus'), (@last_id,'gustavus');

INSERT INTO fn_group (given, new) VALUES ('tamara',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'tam'), (@last_id,'tamara'), (@last_id,'tammy');

INSERT INTO fn_group (given, new) VALUES ('gertrude',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'gert'), (@last_id,'gertie'), (@last_id,'gertrude'), (@last_id,'trudy');

INSERT INTO fn_group (given, new) VALUES ('sharon',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sha'), (@last_id,'sharon'), (@last_id,'shay'), (@last_id,'shaz'), (@last_id,'shaza');

INSERT INTO fn_group (given, new) VALUES ('ian',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ian'), (@last_id,'jack'), (@last_id,'jackie'), (@last_id,'jacques'), (@last_id,'jock'), (@last_id,'john'), (@last_id,'johnnie'), (@last_id,'johnny'), (@last_id,'jon'), (@last_id,'jonathan'), (@last_id,'nat'), (@last_id,'nate'), (@last_id,'nathan');

INSERT INTO fn_group (given, new) VALUES ('delpha',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'delpha'), (@last_id,'delphia'), (@last_id,'philadelphia');

INSERT INTO fn_group (given, new) VALUES ('benedict',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ben'), (@last_id,'benedict'), (@last_id,'bennet'), (@last_id,'bennett'), (@last_id,'bennie');

INSERT INTO fn_group (given, new) VALUES ('deanne',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ann'), (@last_id,'deanne'), (@last_id,'dee');

INSERT INTO fn_group (given, new) VALUES ('hilary',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'hil'), (@last_id,'hilary'), (@last_id,'hilly');

INSERT INTO fn_group (given, new) VALUES ('roderick',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'erick'), (@last_id,'rick'), (@last_id,'rickie'), (@last_id,'rod'), (@last_id,'roddie'), (@last_id,'roddy'), (@last_id,'roderick');

INSERT INTO fn_group (given, new) VALUES ('sigismund',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sig'), (@last_id,'sigismund');

INSERT INTO fn_group (given, new) VALUES ('gilbert',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bert'), (@last_id,'bertie'), (@last_id,'gib'), (@last_id,'gil'), (@last_id,'gilbert'), (@last_id,'wilber'), (@last_id,'will'), (@last_id,'willie');

INSERT INTO fn_group (given, new) VALUES ('almira',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'almira'), (@last_id,'mira'), (@last_id,'myra');

INSERT INTO fn_group (given, new) VALUES ('roseann',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ann'), (@last_id,'rose'), (@last_id,'roseann'), (@last_id,'roseanna'), (@last_id,'rosie'), (@last_id,'roz');

INSERT INTO fn_group (given, new) VALUES ('alfred',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'al'), (@last_id,'alf'), (@last_id,'alfie'), (@last_id,'alfred'), (@last_id,'fred');

INSERT INTO fn_group (given, new) VALUES ('seymour',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'morey'), (@last_id,'see'), (@last_id,'seymour'), (@last_id,'sy');

INSERT INTO fn_group (given, new) VALUES ('randle',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'randle'), (@last_id,'randy');

INSERT INTO fn_group (given, new) VALUES ('alan',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'alan');

INSERT INTO fn_group (given, new) VALUES ('antonia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ann'), (@last_id,'anthony'), (@last_id,'antoinette'), (@last_id,'antone'), (@last_id,'antonia'), (@last_id,'netta'), (@last_id,'nettie'), (@last_id,'netty'), (@last_id,'toni'), (@last_id,'tonie'), (@last_id,'tony'), (@last_id,'tunis');

INSERT INTO fn_group (given, new) VALUES ('jordan',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jordan'), (@last_id,'jordy'), (@last_id,'judd');

INSERT INTO fn_group (given, new) VALUES ('gretchen',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'gretchen'), (@last_id,'margaret');

INSERT INTO fn_group (given, new) VALUES ('shirley',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lee'), (@last_id,'sherry'), (@last_id,'shirl'), (@last_id,'shirley');

INSERT INTO fn_group (given, new) VALUES ('winton',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'wint'), (@last_id,'winton');

INSERT INTO fn_group (given, new) VALUES ('hesther',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'essie'), (@last_id,'hesther');

INSERT INTO fn_group (given, new) VALUES ('penelope',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'nappy'), (@last_id,'neppie'), (@last_id,'penelope'), (@last_id,'penny');

INSERT INTO fn_group (given, new) VALUES ('valerie',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'val'), (@last_id,'valerie');

INSERT INTO fn_group (given, new) VALUES ('roberta',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bert'), (@last_id,'bill'), (@last_id,'billy'), (@last_id,'birdie'), (@last_id,'birtie'), (@last_id,'bob'), (@last_id,'bobbie'), (@last_id,'bobby'), (@last_id,'dob'), (@last_id,'rob'), (@last_id,'robbie'), (@last_id,'robby'), (@last_id,'robert'), (@last_id,'roberta'), (@last_id,'robin'), (@last_id,'rupert');

INSERT INTO fn_group (given, new) VALUES ('jahoda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'hoda'), (@last_id,'jahoda');

INSERT INTO fn_group (given, new) VALUES ('bertram',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bert'), (@last_id,'bertram');

INSERT INTO fn_group (given, new) VALUES ('zachary',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'zachary');

INSERT INTO fn_group (given, new) VALUES ('roberto',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'rob'), (@last_id,'roberto');

INSERT INTO fn_group (given, new) VALUES ('melvina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mel'), (@last_id,'mellie'), (@last_id,'melly'), (@last_id,'melvina'), (@last_id,'vina');

INSERT INTO fn_group (given, new) VALUES ('solomon',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sal'), (@last_id,'salmon'), (@last_id,'saul'), (@last_id,'sol'), (@last_id,'solly'), (@last_id,'solomon'), (@last_id,'zolly');

INSERT INTO fn_group (given, new) VALUES ('pamela',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'pam'), (@last_id,'pamela');

INSERT INTO fn_group (given, new) VALUES ('prescott',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'prescott'), (@last_id,'scott'), (@last_id,'scotty');

INSERT INTO fn_group (given, new) VALUES ('kristopher',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'chris'), (@last_id,'kris'), (@last_id,'kristopher');

INSERT INTO fn_group (given, new) VALUES ('julian',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'julian');

INSERT INTO fn_group (given, new) VALUES ('violet',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'vi'), (@last_id,'violet');

INSERT INTO fn_group (given, new) VALUES ('caleb',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cal'), (@last_id,'cale'), (@last_id,'caleb'), (@last_id,'calep');

INSERT INTO fn_group (given, new) VALUES ('sophronia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'frona'), (@last_id,'fronia'), (@last_id,'sophia'), (@last_id,'sophie'), (@last_id,'sophronia'), (@last_id,'sophy');

INSERT INTO fn_group (given, new) VALUES ('bertha',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bert'), (@last_id,'bertha'), (@last_id,'bertie'), (@last_id,'berty'), (@last_id,'birdie');

INSERT INTO fn_group (given, new) VALUES ('julias',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jule'), (@last_id,'jules'), (@last_id,'julias');

INSERT INTO fn_group (given, new) VALUES ('griselda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'griselda'), (@last_id,'grissel');

INSERT INTO fn_group (given, new) VALUES ('cassandra',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'alex'), (@last_id,'alexa'), (@last_id,'alexandra'), (@last_id,'ali'), (@last_id,'alla'), (@last_id,'cass'), (@last_id,'cassandra'), (@last_id,'cassey'), (@last_id,'cassie'), (@last_id,'lexa'), (@last_id,'lexi'), (@last_id,'lexie'), (@last_id,'sandi'), (@last_id,'sandie'), (@last_id,'sandra'), (@last_id,'sandy');

INSERT INTO fn_group (given, new) VALUES ('lunetta',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lunetta'), (@last_id,'nettie');

INSERT INTO fn_group (given, new) VALUES ('wilson',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'will'), (@last_id,'willie'), (@last_id,'wilson');

INSERT INTO fn_group (given, new) VALUES ('winnifred',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'fred'), (@last_id,'freddie'), (@last_id,'freddy'), (@last_id,'winnie'), (@last_id,'winnifred'), (@last_id,'winny');

INSERT INTO fn_group (given, new) VALUES ('asenath',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'asenath'), (@last_id,'natty');

INSERT INTO fn_group (given, new) VALUES ('judah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'judah'), (@last_id,'jude');

INSERT INTO fn_group (given, new) VALUES ('rudolph',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'rudolph');

INSERT INTO fn_group (given, new) VALUES ('montgomery',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'montgomery'), (@last_id,'monty');

INSERT INTO fn_group (given, new) VALUES ('jefferson',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jeff'), (@last_id,'jefferson'), (@last_id,'sonny');

INSERT INTO fn_group (given, new) VALUES ('rosina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'rosina'), (@last_id,'sina');

INSERT INTO fn_group (given, new) VALUES ('alfreda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'alfreda'), (@last_id,'alfy'), (@last_id,'fred'), (@last_id,'freda'), (@last_id,'freddie'), (@last_id,'freddy'), (@last_id,'frieda');

INSERT INTO fn_group (given, new) VALUES ('serena',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'rena'), (@last_id,'reni'), (@last_id,'serena');

INSERT INTO fn_group (given, new) VALUES ('armanda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'armanda'), (@last_id,'mandy');

INSERT INTO fn_group (given, new) VALUES ('woodrow',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'drew'), (@last_id,'wood'), (@last_id,'woodrow'), (@last_id,'woody');

INSERT INTO fn_group (given, new) VALUES ('victoria',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'tori'), (@last_id,'toria'), (@last_id,'torie'), (@last_id,'torri'), (@last_id,'torrie'), (@last_id,'tory'), (@last_id,'vic'), (@last_id,'vick'), (@last_id,'vicki'), (@last_id,'vickie'), (@last_id,'vicky'), (@last_id,'victor'), (@last_id,'victoria');

INSERT INTO fn_group (given, new) VALUES ('nadine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'deedee'), (@last_id,'nada'), (@last_id,'nadine');

INSERT INTO fn_group (given, new) VALUES ('phyllis',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'phyllis');

INSERT INTO fn_group (given, new) VALUES ('herman',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dutch'), (@last_id,'harman'), (@last_id,'herman');

INSERT INTO fn_group (given, new) VALUES ('enrique',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'enrique'), (@last_id,'quique');

INSERT INTO fn_group (given, new) VALUES ('bradford',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'brad'), (@last_id,'bradford'), (@last_id,'brady'), (@last_id,'ford');

INSERT INTO fn_group (given, new) VALUES ('michelle',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'chelle'), (@last_id,'mica'), (@last_id,'micha'), (@last_id,'miche'), (@last_id,'michelle'), (@last_id,'mickey'), (@last_id,'shell'), (@last_id,'shelly');

INSERT INTO fn_group (given, new) VALUES ('rosalia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'rosalia'), (@last_id,'rosie');

INSERT INTO fn_group (given, new) VALUES ('catriona',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cassie'), (@last_id,'casy'), (@last_id,'cat'), (@last_id,'cate'), (@last_id,'cath'), (@last_id,'catherine'), (@last_id,'cathie'), (@last_id,'cathy'), (@last_id,'catriona'), (@last_id,'karen'), (@last_id,'kate'), (@last_id,'katharine'), (@last_id,'kathie'), (@last_id,'kathleen'), (@last_id,'kathy'), (@last_id,'katie'), (@last_id,'katrine'), (@last_id,'katy'), (@last_id,'kay'), (@last_id,'kit'), (@last_id,'kittie'), (@last_id,'kitty'), (@last_id,'lena'), (@last_id,'rena'), (@last_id,'trina');

INSERT INTO fn_group (given, new) VALUES ('rosalie',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'rosalie'), (@last_id,'rosie');

INSERT INTO fn_group (given, new) VALUES ('lucinda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cindy'), (@last_id,'lou'), (@last_id,'lu'), (@last_id,'lucinda'), (@last_id,'lucy');

INSERT INTO fn_group (given, new) VALUES ('carmon',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cammie'), (@last_id,'carmon'), (@last_id,'charm');

INSERT INTO fn_group (given, new) VALUES ('frederick',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'derick'), (@last_id,'dick'), (@last_id,'fred'), (@last_id,'freddie'), (@last_id,'freddy'), (@last_id,'frederica'), (@last_id,'frederick'), (@last_id,'fredric'), (@last_id,'fritz'), (@last_id,'rick'), (@last_id,'ricky');

INSERT INTO fn_group (given, new) VALUES ('azariah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'azariah'), (@last_id,'riah');

INSERT INTO fn_group (given, new) VALUES ('chester',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'chester'), (@last_id,'chet');

INSERT INTO fn_group (given, new) VALUES ('kristen',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'chris'), (@last_id,'kris'), (@last_id,'krissy'), (@last_id,'kristen');

INSERT INTO fn_group (given, new) VALUES ('abraham',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ab'), (@last_id,'abe'), (@last_id,'abraham'), (@last_id,'abram'), (@last_id,'aby');

INSERT INTO fn_group (given, new) VALUES ('mildred',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mell'), (@last_id,'mildred'), (@last_id,'milly'), (@last_id,'mimi');

INSERT INTO fn_group (given, new) VALUES ('constance',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'connie'), (@last_id,'constance'), (@last_id,'constant');

INSERT INTO fn_group (given, new) VALUES ('agnes',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'agnes');

INSERT INTO fn_group (given, new) VALUES ('thomas',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'tam'), (@last_id,'tammie'), (@last_id,'thom'), (@last_id,'thomas'), (@last_id,'tom'), (@last_id,'tommy'), (@last_id,'tuck'), (@last_id,'tucker');

INSERT INTO fn_group (given, new) VALUES ('vanburen',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'buren'), (@last_id,'vanburen');

INSERT INTO fn_group (given, new) VALUES ('mahala',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'haley'), (@last_id,'hallie'), (@last_id,'huldah'), (@last_id,'mahala'), (@last_id,'mahaley');

INSERT INTO fn_group (given, new) VALUES ('brenda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'brandy'), (@last_id,'brenda');

INSERT INTO fn_group (given, new) VALUES ('lewis',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lew'), (@last_id,'lewie'), (@last_id,'lewis'), (@last_id,'lou'), (@last_id,'louie');

INSERT INTO fn_group (given, new) VALUES ('egbert',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bert'), (@last_id,'burt'), (@last_id,'egbert');

INSERT INTO fn_group (given, new) VALUES ('teresa',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'teresa'), (@last_id,'terry'), (@last_id,'tess'), (@last_id,'tessa'), (@last_id,'tessie'), (@last_id,'thirza'), (@last_id,'thursa'), (@last_id,'tia'), (@last_id,'tracey'), (@last_id,'tracy'), (@last_id,'tyrza');

INSERT INTO fn_group (given, new) VALUES ('bonnie',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bonnie');

INSERT INTO fn_group (given, new) VALUES ('drusilla',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'drucie'), (@last_id,'drusilla'), (@last_id,'ella'), (@last_id,'silla');

INSERT INTO fn_group (given, new) VALUES ('ludovic',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lew'), (@last_id,'lewie'), (@last_id,'lou'), (@last_id,'louie'), (@last_id,'ludovic');

INSERT INTO fn_group (given, new) VALUES ('samyra',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'myra'), (@last_id,'samyra');

INSERT INTO fn_group (given, new) VALUES ('dorothy',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dee'), (@last_id,'dolly'), (@last_id,'dora'), (@last_id,'dorothea'), (@last_id,'dorothy'), (@last_id,'dortha'), (@last_id,'dot'), (@last_id,'dottie'), (@last_id,'dotty');

INSERT INTO fn_group (given, new) VALUES ('naomi',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'naomi'), (@last_id,'omi');

INSERT INTO fn_group (given, new) VALUES ('arminda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'arminda'), (@last_id,'mindie');

INSERT INTO fn_group (given, new) VALUES ('mehitabel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'amabel'), (@last_id,'hetty'), (@last_id,'hitty'), (@last_id,'mabel'), (@last_id,'mehitabel'), (@last_id,'mitty');

INSERT INTO fn_group (given, new) VALUES ('kassandra',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'kassandra'), (@last_id,'kassie');

INSERT INTO fn_group (given, new) VALUES ('adaline',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ada'), (@last_id,'adaline'), (@last_id,'addie'), (@last_id,'addy'), (@last_id,'adela'), (@last_id,'adeline'), (@last_id,'delia'), (@last_id,'dell'), (@last_id,'lena');

INSERT INTO fn_group (given, new) VALUES ('junior',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jr'), (@last_id,'june'), (@last_id,'junie'), (@last_id,'junior'), (@last_id,'junius');

INSERT INTO fn_group (given, new) VALUES ('aldo',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'al'), (@last_id,'aldo');

INSERT INTO fn_group (given, new) VALUES ('otis',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ode'), (@last_id,'otis');

INSERT INTO fn_group (given, new) VALUES ('artemus',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'art'), (@last_id,'artemus');

INSERT INTO fn_group (given, new) VALUES ('lorenzo',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'larry'), (@last_id,'loren'), (@last_id,'lorenzo');

INSERT INTO fn_group (given, new) VALUES ('madeline',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lena'), (@last_id,'maddie'), (@last_id,'maddy'), (@last_id,'madeline'), (@last_id,'madge'), (@last_id,'madie'), (@last_id,'magda'), (@last_id,'maggie'), (@last_id,'maida'), (@last_id,'maud'), (@last_id,'maudlin');

INSERT INTO fn_group (given, new) VALUES ('moses',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'amos'), (@last_id,'mose'), (@last_id,'moses'), (@last_id,'moss');

INSERT INTO fn_group (given, new) VALUES ('caroline',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'caddie'), (@last_id,'car'), (@last_id,'carlotta'), (@last_id,'carol'), (@last_id,'caroline'), (@last_id,'carrie'), (@last_id,'cassie'), (@last_id,'char'), (@last_id,'charlotte'), (@last_id,'lena'), (@last_id,'letty'), (@last_id,'lotta'), (@last_id,'lottie'), (@last_id,'lynn'), (@last_id,'sherry');

INSERT INTO fn_group (given, new) VALUES ('demaris',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dea'), (@last_id,'demaris'), (@last_id,'maris'), (@last_id,'mary');

INSERT INTO fn_group (given, new) VALUES ('monica',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'monica'), (@last_id,'monna'), (@last_id,'monnie');

INSERT INTO fn_group (given, new) VALUES ('roseanna',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'roseanna');

INSERT INTO fn_group (given, new) VALUES ('silas',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'si'), (@last_id,'silas');

INSERT INTO fn_group (given, new) VALUES ('alicia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ailie'), (@last_id,'alicia'), (@last_id,'allie'), (@last_id,'ally'), (@last_id,'elsie'), (@last_id,'lisa');

INSERT INTO fn_group (given, new) VALUES ('temperence',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'temperence'), (@last_id,'tempy');

INSERT INTO fn_group (given, new) VALUES ('christopher',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'chris'), (@last_id,'chrissy'), (@last_id,'christo'), (@last_id,'christopher'), (@last_id,'kester'), (@last_id,'kit'), (@last_id,'topher');

INSERT INTO fn_group (given, new) VALUES ('yvonne',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'vonna'), (@last_id,'yvonne');

INSERT INTO fn_group (given, new) VALUES ('asaph',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'asa'), (@last_id,'asaph');

INSERT INTO fn_group (given, new) VALUES ('johanna',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jo'), (@last_id,'johanna');

INSERT INTO fn_group (given, new) VALUES ('sabrina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bri'), (@last_id,'brina'), (@last_id,'sabra'), (@last_id,'sabrina');

INSERT INTO fn_group (given, new) VALUES ('alexander',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'al'), (@last_id,'alec'), (@last_id,'aleck'), (@last_id,'alex'), (@last_id,'alexander'), (@last_id,'alick'), (@last_id,'andy'), (@last_id,'eleck'), (@last_id,'lex'), (@last_id,'lexie'), (@last_id,'sander'), (@last_id,'sanders'), (@last_id,'sandy'), (@last_id,'xander');

INSERT INTO fn_group (given, new) VALUES ('randal',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'randal'), (@last_id,'randy');

INSERT INTO fn_group (given, new) VALUES ('dianne',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ann'), (@last_id,'di'), (@last_id,'dianne');

INSERT INTO fn_group (given, new) VALUES ('joanne',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jo'), (@last_id,'joanne');

INSERT INTO fn_group (given, new) VALUES ('carolann',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'carol'), (@last_id,'carolann');

INSERT INTO fn_group (given, new) VALUES ('hosea',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'hosea'), (@last_id,'hosey'), (@last_id,'hosie');

INSERT INTO fn_group (given, new) VALUES ('carlotta',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'carlotta');

INSERT INTO fn_group (given, new) VALUES ('parthena',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'parthena'), (@last_id,'thena');

INSERT INTO fn_group (given, new) VALUES ('roger',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'hodge'), (@last_id,'rod'), (@last_id,'rodge'), (@last_id,'rodger'), (@last_id,'rog'), (@last_id,'roger');

INSERT INTO fn_group (given, new) VALUES ('susanna',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'susanna');

INSERT INTO fn_group (given, new) VALUES ('mercedes',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mercedes'), (@last_id,'merci'), (@last_id,'mercy'), (@last_id,'sadie');

INSERT INTO fn_group (given, new) VALUES ('vincent',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'vic'), (@last_id,'vin'), (@last_id,'vince'), (@last_id,'vincent'), (@last_id,'vinnie'), (@last_id,'vinny');

INSERT INTO fn_group (given, new) VALUES ('alzada',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'alzada'), (@last_id,'zada');

INSERT INTO fn_group (given, new) VALUES ('amanda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'amanda'), (@last_id,'manda'), (@last_id,'mandy');

INSERT INTO fn_group (given, new) VALUES ('virginia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ginger'), (@last_id,'ginny'), (@last_id,'jane'), (@last_id,'jennie'), (@last_id,'jenny'), (@last_id,'vergie'), (@last_id,'virgie'), (@last_id,'virginia'), (@last_id,'virgy');

INSERT INTO fn_group (given, new) VALUES ('theophilus',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'theo'), (@last_id,'theophilus');

INSERT INTO fn_group (given, new) VALUES ('caswell',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cass'), (@last_id,'caswell');

INSERT INTO fn_group (given, new) VALUES ('temperance',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'temperance'), (@last_id,'tempy');

INSERT INTO fn_group (given, new) VALUES ('almeda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'almeda'), (@last_id,'mary');

INSERT INTO fn_group (given, new) VALUES ('maurice',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'maurice'), (@last_id,'maury'), (@last_id,'morey'), (@last_id,'morris'), (@last_id,'mossie');

INSERT INTO fn_group (given, new) VALUES ('isabel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'isabel');

INSERT INTO fn_group (given, new) VALUES ('campbell',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cam'), (@last_id,'campbell');

INSERT INTO fn_group (given, new) VALUES ('elnora',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'elnora'), (@last_id,'nora');

INSERT INTO fn_group (given, new) VALUES ('leslie',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'les'), (@last_id,'leslie'), (@last_id,'lester'), (@last_id,'lizzy');

INSERT INTO fn_group (given, new) VALUES ('delores',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dee'), (@last_id,'dell'), (@last_id,'della'), (@last_id,'delores'), (@last_id,'dodie'), (@last_id,'lola'), (@last_id,'lolly');

INSERT INTO fn_group (given, new) VALUES ('araminta',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'araminta'), (@last_id,'armida'), (@last_id,'middie');

INSERT INTO fn_group (given, new) VALUES ('madison',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'maddie'), (@last_id,'maddy'), (@last_id,'madison'), (@last_id,'mattie');

INSERT INTO fn_group (given, new) VALUES ('cornelius',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'con'), (@last_id,'conny'), (@last_id,'cornelia'), (@last_id,'cornelius'), (@last_id,'cornie'), (@last_id,'corny'), (@last_id,'neely'), (@last_id,'neil'), (@last_id,'nelia'), (@last_id,'nelle'), (@last_id,'nelly'), (@last_id,'niel');

INSERT INTO fn_group (given, new) VALUES ('eudora',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dora'), (@last_id,'eudora');

INSERT INTO fn_group (given, new) VALUES ('irene',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'irene'), (@last_id,'rena'), (@last_id,'rennie');

INSERT INTO fn_group (given, new) VALUES ('zechariah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'zach'), (@last_id,'zak'), (@last_id,'zechariah');

INSERT INTO fn_group (given, new) VALUES ('manola',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'manola'), (@last_id,'nonnie');

INSERT INTO fn_group (given, new) VALUES ('donato',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'don'), (@last_id,'donato');

INSERT INTO fn_group (given, new) VALUES ('marsha',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'marcie'), (@last_id,'marsha'), (@last_id,'mary');

INSERT INTO fn_group (given, new) VALUES ('melissa',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ally'), (@last_id,'alyssa'), (@last_id,'issa'), (@last_id,'lisa'), (@last_id,'lissa'), (@last_id,'lyssa'), (@last_id,'mel'), (@last_id,'melissa'), (@last_id,'milly'), (@last_id,'missy');

INSERT INTO fn_group (given, new) VALUES ('winfield',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'field'), (@last_id,'win'), (@last_id,'winfield'), (@last_id,'winny');

INSERT INTO fn_group (given, new) VALUES ('marvin',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'marv'), (@last_id,'marvin'), (@last_id,'merv'), (@last_id,'mervyn');

INSERT INTO fn_group (given, new) VALUES ('elsie',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'elsie');

INSERT INTO fn_group (given, new) VALUES ('nathaniel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jonathan'), (@last_id,'nat'), (@last_id,'nate'), (@last_id,'nath'), (@last_id,'nathan'), (@last_id,'nathaniel'), (@last_id,'natty'), (@last_id,'tan'), (@last_id,'than');

INSERT INTO fn_group (given, new) VALUES ('madelyn',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'maddy'), (@last_id,'madelyn'), (@last_id,'madie');

INSERT INTO fn_group (given, new) VALUES ('clarinda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'clara'), (@last_id,'clarinda');

INSERT INTO fn_group (given, new) VALUES ('frances',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cissy'), (@last_id,'fan'), (@last_id,'fannie'), (@last_id,'fanny'), (@last_id,'fran'), (@last_id,'france'), (@last_id,'frances'), (@last_id,'francie'), (@last_id,'francis'), (@last_id,'frank'), (@last_id,'frankie'), (@last_id,'frannie'), (@last_id,'franny'), (@last_id,'franz');

INSERT INTO fn_group (given, new) VALUES ('laurinda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'larry'), (@last_id,'lars'), (@last_id,'laura'), (@last_id,'laurence'), (@last_id,'laurinda'), (@last_id,'lawrence'), (@last_id,'lawrie'), (@last_id,'lon'), (@last_id,'lonny'), (@last_id,'lorne'), (@last_id,'lorry');

INSERT INTO fn_group (given, new) VALUES ('petronella',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'nellie'), (@last_id,'petronella');

INSERT INTO fn_group (given, new) VALUES ('persephone',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'persephone'), (@last_id,'seph');

INSERT INTO fn_group (given, new) VALUES ('tobias',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'tobe'), (@last_id,'tobias'), (@last_id,'toby');

INSERT INTO fn_group (given, new) VALUES ('theresa',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'terry'), (@last_id,'tess'), (@last_id,'tessa'), (@last_id,'tessie'), (@last_id,'theresa'), (@last_id,'thirza'), (@last_id,'thursa'), (@last_id,'tia'), (@last_id,'tracey'), (@last_id,'tracie'), (@last_id,'tracy');

INSERT INTO fn_group (given, new) VALUES ('clayton',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'clay'), (@last_id,'clayton');

INSERT INTO fn_group (given, new) VALUES ('elijah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'eli'), (@last_id,'elijah'), (@last_id,'lige');

INSERT INTO fn_group (given, new) VALUES ('pinckney',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'pinckney'), (@last_id,'pink');

INSERT INTO fn_group (given, new) VALUES ('belinda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'belinda'), (@last_id,'bell'), (@last_id,'belle'), (@last_id,'linda'), (@last_id,'lindy'), (@last_id,'lynn'), (@last_id,'mel'), (@last_id,'melinda'), (@last_id,'mindy'), (@last_id,'rosalinda');

INSERT INTO fn_group (given, new) VALUES ('cedric',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ced'), (@last_id,'cedric'), (@last_id,'rick'), (@last_id,'ricky');

INSERT INTO fn_group (given, new) VALUES ('aloysius',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'aloysius'), (@last_id,'lewie'), (@last_id,'lou');

INSERT INTO fn_group (given, new) VALUES ('marilyn',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'marilyn'), (@last_id,'mary');

INSERT INTO fn_group (given, new) VALUES ('wilbur',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'wilbur'), (@last_id,'will'), (@last_id,'willie');

INSERT INTO fn_group (given, new) VALUES ('norbert',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bert'), (@last_id,'norbert'), (@last_id,'norby');

INSERT INTO fn_group (given, new) VALUES ('adelbert',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ad'), (@last_id,'ade'), (@last_id,'adelbert'), (@last_id,'al'), (@last_id,'albert'), (@last_id,'albie'), (@last_id,'bert'), (@last_id,'bertie'), (@last_id,'bird'), (@last_id,'birt'), (@last_id,'del'), (@last_id,'delbert'), (@last_id,'elbert');

INSERT INTO fn_group (given, new) VALUES ('christiana',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'chris'), (@last_id,'christiana'), (@last_id,'christy'), (@last_id,'crissy'), (@last_id,'kris'), (@last_id,'kristy'), (@last_id,'tina');

INSERT INTO fn_group (given, new) VALUES ('edgar',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ed'), (@last_id,'eddie'), (@last_id,'eddy'), (@last_id,'edgar');

INSERT INTO fn_group (given, new) VALUES ('thom',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'thom');

INSERT INTO fn_group (given, new) VALUES ('juanita',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'juanita'), (@last_id,'nettie'), (@last_id,'nita');

INSERT INTO fn_group (given, new) VALUES ('marietta',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mae'), (@last_id,'mamie'), (@last_id,'maria'), (@last_id,'mariah'), (@last_id,'marie'), (@last_id,'marietta'), (@last_id,'marion'), (@last_id,'mary'), (@last_id,'maureen'), (@last_id,'may'), (@last_id,'mercy'), (@last_id,'minnie'), (@last_id,'mitzi'), (@last_id,'mollie'), (@last_id,'molly'), (@last_id,'polly');

INSERT INTO fn_group (given, new) VALUES ('casimir',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'casimir'), (@last_id,'cassie');

INSERT INTO fn_group (given, new) VALUES ('elinor',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'elinor'), (@last_id,'ella'), (@last_id,'ellen'), (@last_id,'nell'), (@last_id,'nellie'), (@last_id,'nora'), (@last_id,'norah');

INSERT INTO fn_group (given, new) VALUES ('tobiah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'tobiah'), (@last_id,'toby');

INSERT INTO fn_group (given, new) VALUES ('bedelia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bedelia'), (@last_id,'bridgit'), (@last_id,'delia');

INSERT INTO fn_group (given, new) VALUES ('sullivan',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sullivan'), (@last_id,'sully'), (@last_id,'van');

INSERT INTO fn_group (given, new) VALUES ('smith',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'smith'), (@last_id,'smitty');

INSERT INTO fn_group (given, new) VALUES ('celeste',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'celeste'), (@last_id,'celia'), (@last_id,'lessie');

INSERT INTO fn_group (given, new) VALUES ('darlene',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dara'), (@last_id,'darlene'), (@last_id,'darry'), (@last_id,'lena');

INSERT INTO fn_group (given, new) VALUES ('lauryn',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'laurie'), (@last_id,'lauryn');

INSERT INTO fn_group (given, new) VALUES ('denise',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'denise'), (@last_id,'dennie'), (@last_id,'dennis'), (@last_id,'denny');

INSERT INTO fn_group (given, new) VALUES ('johann',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'johann'), (@last_id,'john');

INSERT INTO fn_group (given, new) VALUES ('tennessee',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'tennessee'), (@last_id,'tennie'), (@last_id,'tenny');

INSERT INTO fn_group (given, new) VALUES ('rodger',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'rodger');

INSERT INTO fn_group (given, new) VALUES ('manerva',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'eve'), (@last_id,'manerva'), (@last_id,'mina'), (@last_id,'minerva'), (@last_id,'minnie'), (@last_id,'nerva'), (@last_id,'nervie');

INSERT INTO fn_group (given, new) VALUES ('katarina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'catherine'), (@last_id,'katarina'), (@last_id,'tina');

INSERT INTO fn_group (given, new) VALUES ('priscilla',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cilla'), (@last_id,'cissy'), (@last_id,'priscilla'), (@last_id,'prissy'), (@last_id,'siller');

INSERT INTO fn_group (given, new) VALUES ('howard',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'hal'), (@last_id,'howard'), (@last_id,'howie');

INSERT INTO fn_group (given, new) VALUES ('sigfrid',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sid'), (@last_id,'sigfrid');

INSERT INTO fn_group (given, new) VALUES ('mark',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mark');

INSERT INTO fn_group (given, new) VALUES ('johannes',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'johannes'), (@last_id,'jonathan');

INSERT INTO fn_group (given, new) VALUES ('geoffrey',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'geoff'), (@last_id,'geoffrey'), (@last_id,'jeff');

INSERT INTO fn_group (given, new) VALUES ('rhoda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'rhoda'), (@last_id,'rodie');

INSERT INTO fn_group (given, new) VALUES ('michele',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'michele'), (@last_id,'shell');

INSERT INTO fn_group (given, new) VALUES ('walter',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'wally'), (@last_id,'walt'), (@last_id,'walter'), (@last_id,'wat');

INSERT INTO fn_group (given, new) VALUES ('selina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lena'), (@last_id,'sally'), (@last_id,'selina');

INSERT INTO fn_group (given, new) VALUES ('kingston',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'king'), (@last_id,'kingston');

INSERT INTO fn_group (given, new) VALUES ('calista',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'calista'), (@last_id,'kissy');

INSERT INTO fn_group (given, new) VALUES ('aubrey',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'aubrey'), (@last_id,'bree');

INSERT INTO fn_group (given, new) VALUES ('hermione',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'hermie'), (@last_id,'hermione');

INSERT INTO fn_group (given, new) VALUES ('eduardo',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ed'), (@last_id,'eddie'), (@last_id,'eddy'), (@last_id,'eduardo');

INSERT INTO fn_group (given, new) VALUES ('courtney',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'corky'), (@last_id,'court'), (@last_id,'courtney'), (@last_id,'curt');

INSERT INTO fn_group (given, new) VALUES ('anthony',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'anthony');

INSERT INTO fn_group (given, new) VALUES ('isaac',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ike'), (@last_id,'isaac'), (@last_id,'zeke');

INSERT INTO fn_group (given, new) VALUES ('katherine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'caitlin'), (@last_id,'cassie'), (@last_id,'casy'), (@last_id,'cat'), (@last_id,'cathie'), (@last_id,'cathy'), (@last_id,'karen'), (@last_id,'kat'), (@last_id,'kate'), (@last_id,'kath'), (@last_id,'katharine'), (@last_id,'katherine'), (@last_id,'kathie'), (@last_id,'kathleen'), (@last_id,'kathy'), (@last_id,'katie'), (@last_id,'katrine'), (@last_id,'katy'), (@last_id,'kay'), (@last_id,'kaye'), (@last_id,'kit'), (@last_id,'kittie'), (@last_id,'kitty'), (@last_id,'lena'), (@last_id,'rina'), (@last_id,'terri'), (@last_id,'trina');

INSERT INTO fn_group (given, new) VALUES ('andrea',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'andrea'), (@last_id,'andrew'), (@last_id,'andy'), (@last_id,'drea'), (@last_id,'drew'), (@last_id,'rea');

INSERT INTO fn_group (given, new) VALUES ('aileen',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'aileen');

INSERT INTO fn_group (given, new) VALUES ('napoleon',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'leon'), (@last_id,'nap'), (@last_id,'napoleon'), (@last_id,'nappy');

INSERT INTO fn_group (given, new) VALUES ('lionel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'leon'), (@last_id,'lionel');

INSERT INTO fn_group (given, new) VALUES ('winifred',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'freddie'), (@last_id,'winifred'), (@last_id,'winnie');

INSERT INTO fn_group (given, new) VALUES ('sheridan',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dan'), (@last_id,'danny'), (@last_id,'sher'), (@last_id,'sheridan');

INSERT INTO fn_group (given, new) VALUES ('georgia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'george'), (@last_id,'georgia'), (@last_id,'georgiana');

INSERT INTO fn_group (given, new) VALUES ('sampson',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sam'), (@last_id,'sampson');

INSERT INTO fn_group (given, new) VALUES ('isabelle',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'isabelle');

INSERT INTO fn_group (given, new) VALUES ('valeri',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'val'), (@last_id,'valeri');

INSERT INTO fn_group (given, new) VALUES ('garfield',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'gal'), (@last_id,'garfield'), (@last_id,'garry'), (@last_id,'gary');

INSERT INTO fn_group (given, new) VALUES ('arnold',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'arnie'), (@last_id,'arnold');

INSERT INTO fn_group (given, new) VALUES ('elouise',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'eliza'), (@last_id,'eloise'), (@last_id,'elouise'), (@last_id,'heloise'), (@last_id,'issie'), (@last_id,'lew'), (@last_id,'lewie'), (@last_id,'lois'), (@last_id,'lou'), (@last_id,'louie'), (@last_id,'louis'), (@last_id,'louisa'), (@last_id,'louise'), (@last_id,'lulu');

INSERT INTO fn_group (given, new) VALUES ('abiel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ab'), (@last_id,'abiel');

INSERT INTO fn_group (given, new) VALUES ('dominic',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dom'), (@last_id,'dominic'), (@last_id,'nick'), (@last_id,'nicky');

INSERT INTO fn_group (given, new) VALUES ('antony',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'antony'), (@last_id,'tony');

INSERT INTO fn_group (given, new) VALUES ('medora',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dora'), (@last_id,'medora');

INSERT INTO fn_group (given, new) VALUES ('raymund',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ray'), (@last_id,'raymund');

INSERT INTO fn_group (given, new) VALUES ('jeanette',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'janet'), (@last_id,'jean'), (@last_id,'jeanette'), (@last_id,'jeanne'), (@last_id,'jessie'), (@last_id,'nettie');

INSERT INTO fn_group (given, new) VALUES ('veronica',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'franky'), (@last_id,'ron'), (@last_id,'ronna'), (@last_id,'ronni'), (@last_id,'ronnie'), (@last_id,'ronny'), (@last_id,'vera'), (@last_id,'veronica'), (@last_id,'vonnie');

INSERT INTO fn_group (given, new) VALUES ('jemima',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jemima'), (@last_id,'jemma'), (@last_id,'mima'), (@last_id,'mimi');

INSERT INTO fn_group (given, new) VALUES ('iva',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'iva'), (@last_id,'ivy');

INSERT INTO fn_group (given, new) VALUES ('evelyn',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'evelyn');

INSERT INTO fn_group (given, new) VALUES ('candace',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'candace'), (@last_id,'candy'), (@last_id,'dacey');

INSERT INTO fn_group (given, new) VALUES ('calvin',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cal'), (@last_id,'calvin'), (@last_id,'vin'), (@last_id,'vinny');

INSERT INTO fn_group (given, new) VALUES ('audrey',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'audrey'), (@last_id,'dee');

INSERT INTO fn_group (given, new) VALUES ('ellender',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ellender');

INSERT INTO fn_group (given, new) VALUES ('allen',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'al'), (@last_id,'allen');

INSERT INTO fn_group (given, new) VALUES ('allison',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'al'), (@last_id,'ali'), (@last_id,'allison'), (@last_id,'ally');

INSERT INTO fn_group (given, new) VALUES ('lillah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lil'), (@last_id,'lillah'), (@last_id,'lilly'), (@last_id,'lily'), (@last_id,'lolly');

INSERT INTO fn_group (given, new) VALUES ('dawson',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dawson'), (@last_id,'doss');

INSERT INTO fn_group (given, new) VALUES ('armena',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'armena'), (@last_id,'arry'), (@last_id,'mena');

INSERT INTO fn_group (given, new) VALUES ('roscoe',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'roscoe'), (@last_id,'ross');

INSERT INTO fn_group (given, new) VALUES ('roderic',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'rick'), (@last_id,'rod'), (@last_id,'roddie'), (@last_id,'roddy'), (@last_id,'roderic');

INSERT INTO fn_group (given, new) VALUES ('jennet',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jennet'), (@last_id,'jenny'), (@last_id,'jessie');

INSERT INTO fn_group (given, new) VALUES ('matthew',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mat'), (@last_id,'matt'), (@last_id,'matthew'), (@last_id,'matthias'), (@last_id,'matty');

INSERT INTO fn_group (given, new) VALUES ('viola',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ola'), (@last_id,'vi'), (@last_id,'viola');

INSERT INTO fn_group (given, new) VALUES ('samson',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sam'), (@last_id,'samson');

INSERT INTO fn_group (given, new) VALUES ('alfonse',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'al'), (@last_id,'alfonse');

INSERT INTO fn_group (given, new) VALUES ('jeffrey',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'geoff'), (@last_id,'jeff'), (@last_id,'jeffrey');

INSERT INTO fn_group (given, new) VALUES ('raymond',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ray'), (@last_id,'raymond');

INSERT INTO fn_group (given, new) VALUES ('theodore',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dora'), (@last_id,'dorey'), (@last_id,'ed'), (@last_id,'eddy'), (@last_id,'ned'), (@last_id,'neddy'), (@last_id,'ted'), (@last_id,'teddi'), (@last_id,'teddy'), (@last_id,'theo'), (@last_id,'theodora'), (@last_id,'theodore');

INSERT INTO fn_group (given, new) VALUES ('samantha',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sam'), (@last_id,'samantha'), (@last_id,'sammy');

INSERT INTO fn_group (given, new) VALUES ('ella',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ella');

INSERT INTO fn_group (given, new) VALUES ('edythe',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'edie'), (@last_id,'edye'), (@last_id,'edythe');

INSERT INTO fn_group (given, new) VALUES ('theodora',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'theodora');

INSERT INTO fn_group (given, new) VALUES ('lyndon',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lindy'), (@last_id,'lyndon'), (@last_id,'lynn');

INSERT INTO fn_group (given, new) VALUES ('deidre',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'deedee'), (@last_id,'deidre');

INSERT INTO fn_group (given, new) VALUES ('gloria',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'gloria'), (@last_id,'glory');

INSERT INTO fn_group (given, new) VALUES ('kimberley',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'kim'), (@last_id,'kimberley');

INSERT INTO fn_group (given, new) VALUES ('rosabel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bella'), (@last_id,'belle'), (@last_id,'rosa'), (@last_id,'rosabel'), (@last_id,'rosabella'), (@last_id,'rose'), (@last_id,'rosie'), (@last_id,'roz');

INSERT INTO fn_group (given, new) VALUES ('eurydice',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dicey'), (@last_id,'eurydice');

INSERT INTO fn_group (given, new) VALUES ('lucas',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lucas'), (@last_id,'lucias'), (@last_id,'luke'), (@last_id,'luther');

INSERT INTO fn_group (given, new) VALUES ('lamont',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lamont'), (@last_id,'monty');

INSERT INTO fn_group (given, new) VALUES ('harriot',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'harriot'), (@last_id,'hatty');

INSERT INTO fn_group (given, new) VALUES ('zephaniah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'zeph'), (@last_id,'zephaniah');

INSERT INTO fn_group (given, new) VALUES ('jedediah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dyer'), (@last_id,'jed'), (@last_id,'jedediah');

INSERT INTO fn_group (given, new) VALUES ('levi',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lee'), (@last_id,'levi');

INSERT INTO fn_group (given, new) VALUES ('corinne',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cora'), (@last_id,'corinne');

INSERT INTO fn_group (given, new) VALUES ('jessica',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jess'), (@last_id,'jesse'), (@last_id,'jessica'), (@last_id,'jessie');

INSERT INTO fn_group (given, new) VALUES ('pandora',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dora'), (@last_id,'pandora');

INSERT INTO fn_group (given, new) VALUES ('olivia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'libby'), (@last_id,'liv'), (@last_id,'livia'), (@last_id,'livvie'), (@last_id,'nollie'), (@last_id,'nolly'), (@last_id,'obbie'), (@last_id,'oli'), (@last_id,'olive'), (@last_id,'oliver'), (@last_id,'olivia'), (@last_id,'ollie'), (@last_id,'olly');

INSERT INTO fn_group (given, new) VALUES ('spencer',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'spence'), (@last_id,'spencer');

INSERT INTO fn_group (given, new) VALUES ('monique',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mon'), (@last_id,'monike'), (@last_id,'monique');

INSERT INTO fn_group (given, new) VALUES ('sarahjane',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dane'), (@last_id,'sarahjane');

INSERT INTO fn_group (given, new) VALUES ('sheila',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cecil'), (@last_id,'cecilia'), (@last_id,'celia'), (@last_id,'cissie'), (@last_id,'cissy'), (@last_id,'sheila'), (@last_id,'sisely'), (@last_id,'sissie');

INSERT INTO fn_group (given, new) VALUES ('ignatius',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'iggy'), (@last_id,'ignatius'), (@last_id,'nace'), (@last_id,'nate');

INSERT INTO fn_group (given, new) VALUES ('monet',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'monet'), (@last_id,'nettie');

INSERT INTO fn_group (given, new) VALUES ('savannah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'anna'), (@last_id,'savannah'), (@last_id,'vannie');

INSERT INTO fn_group (given, new) VALUES ('newton',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'newt'), (@last_id,'newton');

INSERT INTO fn_group (given, new) VALUES ('lester',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lester');

INSERT INTO fn_group (given, new) VALUES ('roxane',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'rox'), (@last_id,'roxane'), (@last_id,'roxie');

INSERT INTO fn_group (given, new) VALUES ('thaddeus',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'tad'), (@last_id,'thad'), (@last_id,'thaddeus');

INSERT INTO fn_group (given, new) VALUES ('rebekah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'beck'), (@last_id,'becky'), (@last_id,'rebekah');

INSERT INTO fn_group (given, new) VALUES ('roxana',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'roxana'), (@last_id,'roxy');

INSERT INTO fn_group (given, new) VALUES ('casper',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'casper');

INSERT INTO fn_group (given, new) VALUES ('almina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'almina'), (@last_id,'minnie');

INSERT INTO fn_group (given, new) VALUES ('paul',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lina'), (@last_id,'paul'), (@last_id,'paula'), (@last_id,'paulie'), (@last_id,'pauline'), (@last_id,'polly');

INSERT INTO fn_group (given, new) VALUES ('lauren',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lauren'), (@last_id,'laurie'), (@last_id,'ren');

INSERT INTO fn_group (given, new) VALUES ('shaina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sha'), (@last_id,'shaina'), (@last_id,'shay');

INSERT INTO fn_group (given, new) VALUES ('nicolas',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'nic'), (@last_id,'nick'), (@last_id,'nicolas');

INSERT INTO fn_group (given, new) VALUES ('jose',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jose'), (@last_id,'pepe');

INSERT INTO fn_group (given, new) VALUES ('almena',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'allie'), (@last_id,'almena'), (@last_id,'mena');

INSERT INTO fn_group (given, new) VALUES ('sheldon',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sheldon'), (@last_id,'shelly');

INSERT INTO fn_group (given, new) VALUES ('melinda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'melinda');

INSERT INTO fn_group (given, new) VALUES ('nicodemus',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'nick'), (@last_id,'nicodemus');

INSERT INTO fn_group (given, new) VALUES ('genevieve',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'eve'), (@last_id,'genevieve'), (@last_id,'jean'), (@last_id,'jenny');

INSERT INTO fn_group (given, new) VALUES ('kimberly',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'kim'), (@last_id,'kimberly');

INSERT INTO fn_group (given, new) VALUES ('greenberry',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'berry'), (@last_id,'green'), (@last_id,'greenberry');

INSERT INTO fn_group (given, new) VALUES ('sydney',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sid'), (@last_id,'sydney');

INSERT INTO fn_group (given, new) VALUES ('johannah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'johannah');

INSERT INTO fn_group (given, new) VALUES ('duncan',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'duncan'), (@last_id,'dunk');

INSERT INTO fn_group (given, new) VALUES ('dorcus',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'darcus'), (@last_id,'dorcus');

INSERT INTO fn_group (given, new) VALUES ('malcolm',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mac'), (@last_id,'mal'), (@last_id,'malcolm');

INSERT INTO fn_group (given, new) VALUES ('minerva',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'minerva');

INSERT INTO fn_group (given, new) VALUES ('gustav',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'gus'), (@last_id,'gustav');

INSERT INTO fn_group (given, new) VALUES ('vanessa',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'essa'), (@last_id,'nessa'), (@last_id,'nessie'), (@last_id,'van'), (@last_id,'vanessa'), (@last_id,'vanna'), (@last_id,'vannie');

INSERT INTO fn_group (given, new) VALUES ('adolph',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'adolph');

INSERT INTO fn_group (given, new) VALUES ('christina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'chris'), (@last_id,'chrissie'), (@last_id,'christina'), (@last_id,'christy'), (@last_id,'crissy'), (@last_id,'kris'), (@last_id,'kristy'), (@last_id,'teenie'), (@last_id,'tina'), (@last_id,'xina');

INSERT INTO fn_group (given, new) VALUES ('earnest',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'earnest'), (@last_id,'erna'), (@last_id,'ernest'), (@last_id,'ernestine'), (@last_id,'ernie'), (@last_id,'tina');

INSERT INTO fn_group (given, new) VALUES ('emmeline',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'emmeline'), (@last_id,'emmie');

INSERT INTO fn_group (given, new) VALUES ('dennison',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dennie'), (@last_id,'dennis'), (@last_id,'dennison'), (@last_id,'denny');

INSERT INTO fn_group (given, new) VALUES ('lavonne',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lavonne'), (@last_id,'von');

INSERT INTO fn_group (given, new) VALUES ('rebecca',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'becca'), (@last_id,'beck'), (@last_id,'beckie'), (@last_id,'becky'), (@last_id,'reba'), (@last_id,'rebecca');

INSERT INTO fn_group (given, new) VALUES ('reginald',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'naldo'), (@last_id,'reg'), (@last_id,'reggie'), (@last_id,'reginald'), (@last_id,'renny'), (@last_id,'rex'), (@last_id,'reynold');

INSERT INTO fn_group (given, new) VALUES ('cleatus',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cleat'), (@last_id,'cleatus');

INSERT INTO fn_group (given, new) VALUES ('mariah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mariah');

INSERT INTO fn_group (given, new) VALUES ('lincoln',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lincoln'), (@last_id,'link');

INSERT INTO fn_group (given, new) VALUES ('izaak',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ike'), (@last_id,'izaak');

INSERT INTO fn_group (given, new) VALUES ('emma',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'emma');

INSERT INTO fn_group (given, new) VALUES ('christine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'chris'), (@last_id,'chrissie'), (@last_id,'chrissy'), (@last_id,'christi'), (@last_id,'christine'), (@last_id,'christy'), (@last_id,'crissy'), (@last_id,'kris'), (@last_id,'kristy'), (@last_id,'tina');

INSERT INTO fn_group (given, new) VALUES ('marian',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'marian'), (@last_id,'marianna'), (@last_id,'marion');

INSERT INTO fn_group (given, new) VALUES ('patience',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'pat'), (@last_id,'patience'), (@last_id,'patty');

INSERT INTO fn_group (given, new) VALUES ('lavonia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lavonia'), (@last_id,'vina'), (@last_id,'viney'), (@last_id,'vonnie');

INSERT INTO fn_group (given, new) VALUES ('orlando',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lanny'), (@last_id,'orlando'), (@last_id,'roland'), (@last_id,'rollo'), (@last_id,'rolly'), (@last_id,'rowland');

INSERT INTO fn_group (given, new) VALUES ('alice',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'alice');

INSERT INTO fn_group (given, new) VALUES ('abiah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ab'), (@last_id,'abiah');

INSERT INTO fn_group (given, new) VALUES ('arthur',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'art'), (@last_id,'arthur');

INSERT INTO fn_group (given, new) VALUES ('lucretia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lucretia'), (@last_id,'lucy');

INSERT INTO fn_group (given, new) VALUES ('gwendolyn',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'genny'), (@last_id,'gwen'), (@last_id,'gwendolyn'), (@last_id,'wen'), (@last_id,'wendy');

INSERT INTO fn_group (given, new) VALUES ('martin',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'martin'), (@last_id,'marty');

INSERT INTO fn_group (given, new) VALUES ('beverly',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bev'), (@last_id,'beverly');

INSERT INTO fn_group (given, new) VALUES ('isadora',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dora'), (@last_id,'isadora'), (@last_id,'isidore'), (@last_id,'issy'), (@last_id,'izzy');

INSERT INTO fn_group (given, new) VALUES ('conrad',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'con'), (@last_id,'connie'), (@last_id,'conny'), (@last_id,'conrad');

INSERT INTO fn_group (given, new) VALUES ('clementine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'clem'), (@last_id,'clement'), (@last_id,'clementine');

INSERT INTO fn_group (given, new) VALUES ('alabama',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'al'), (@last_id,'alabama'), (@last_id,'bama');

INSERT INTO fn_group (given, new) VALUES ('lemuel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lem'), (@last_id,'lemuel');

INSERT INTO fn_group (given, new) VALUES ('paulina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lina'), (@last_id,'paulina'), (@last_id,'polly');

INSERT INTO fn_group (given, new) VALUES ('fredericka',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'fred'), (@last_id,'freda'), (@last_id,'freddy'), (@last_id,'fredericka'), (@last_id,'frieda'), (@last_id,'ricka');

INSERT INTO fn_group (given, new) VALUES ('pauline',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'pauline');

INSERT INTO fn_group (given, new) VALUES ('zebediah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dyer'), (@last_id,'zebediah'), (@last_id,'zed');

INSERT INTO fn_group (given, new) VALUES ('jennifer',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jan'), (@last_id,'jen'), (@last_id,'jenna'), (@last_id,'jennie'), (@last_id,'jennifer'), (@last_id,'jenny');

INSERT INTO fn_group (given, new) VALUES ('aldrich',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'al'), (@last_id,'aldrich'), (@last_id,'rich'), (@last_id,'richie');

INSERT INTO fn_group (given, new) VALUES ('manuel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'manuel');

INSERT INTO fn_group (given, new) VALUES ('donald',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'don'), (@last_id,'donald'), (@last_id,'donnie'), (@last_id,'donny');

INSERT INTO fn_group (given, new) VALUES ('jeffery',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jeff'), (@last_id,'jeffery');

INSERT INTO fn_group (given, new) VALUES ('lettice',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lettice');

INSERT INTO fn_group (given, new) VALUES ('eugenia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'eugenia');

INSERT INTO fn_group (given, new) VALUES ('ursula',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sula'), (@last_id,'sulie'), (@last_id,'ursula');

INSERT INTO fn_group (given, new) VALUES ('jeremy',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jeremy');

INSERT INTO fn_group (given, new) VALUES ('arlene',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'arlene'), (@last_id,'arly'), (@last_id,'lena');

INSERT INTO fn_group (given, new) VALUES ('willis',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bill'), (@last_id,'willis'), (@last_id,'willy');

INSERT INTO fn_group (given, new) VALUES ('morris',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'morris');

INSERT INTO fn_group (given, new) VALUES ('debra',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'deb'), (@last_id,'debbie'), (@last_id,'debra');

INSERT INTO fn_group (given, new) VALUES ('lorraine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lorie'), (@last_id,'lorraine'), (@last_id,'lorrie');

INSERT INTO fn_group (given, new) VALUES ('nicole',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cole'), (@last_id,'colie'), (@last_id,'nicole'), (@last_id,'nikki'), (@last_id,'nole');

INSERT INTO fn_group (given, new) VALUES ('wendy',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'wendy');

INSERT INTO fn_group (given, new) VALUES ('levone',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'levone'), (@last_id,'von');

INSERT INTO fn_group (given, new) VALUES ('lurana',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lura'), (@last_id,'lurana');

INSERT INTO fn_group (given, new) VALUES ('anna',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'anna');

INSERT INTO fn_group (given, new) VALUES ('columbus',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'columbus'), (@last_id,'lum');

INSERT INTO fn_group (given, new) VALUES ('aeneas',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'aeneas'), (@last_id,'eneas');

INSERT INTO fn_group (given, new) VALUES ('vivian',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'vi'), (@last_id,'viv'), (@last_id,'vivian');

INSERT INTO fn_group (given, new) VALUES ('camile',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'camile'), (@last_id,'cammie');

INSERT INTO fn_group (given, new) VALUES ('mervin',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'merv'), (@last_id,'mervin');

INSERT INTO fn_group (given, new) VALUES ('millicent',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'millicent'), (@last_id,'milly'), (@last_id,'missy');

INSERT INTO fn_group (given, new) VALUES ('ivan',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ivan'), (@last_id,'john');

INSERT INTO fn_group (given, new) VALUES ('kristin',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'chris'), (@last_id,'kris'), (@last_id,'kristin'), (@last_id,'kristy');

INSERT INTO fn_group (given, new) VALUES ('piper',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'pi'), (@last_id,'piper');

INSERT INTO fn_group (given, new) VALUES ('judson',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jud'), (@last_id,'judson'), (@last_id,'sonny');

INSERT INTO fn_group (given, new) VALUES ('francis',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'francis');

INSERT INTO fn_group (given, new) VALUES ('clementina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'clement'), (@last_id,'clementina');

INSERT INTO fn_group (given, new) VALUES ('anselm',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ance'), (@last_id,'ansel'), (@last_id,'anselm'), (@last_id,'selma');

INSERT INTO fn_group (given, new) VALUES ('phillip',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'phil'), (@last_id,'phillip');

INSERT INTO fn_group (given, new) VALUES ('faith',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'faith'), (@last_id,'fay');

INSERT INTO fn_group (given, new) VALUES ('wilhelmena',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mina'), (@last_id,'minnie'), (@last_id,'wilhelmena'), (@last_id,'wilmot');

INSERT INTO fn_group (given, new) VALUES ('barnaby',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'barnaby'), (@last_id,'barney');

INSERT INTO fn_group (given, new) VALUES ('magdelina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lena'), (@last_id,'madge'), (@last_id,'magda'), (@last_id,'magdelina'), (@last_id,'maggie');

INSERT INTO fn_group (given, new) VALUES ('brittany',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'britt'), (@last_id,'brittany');

INSERT INTO fn_group (given, new) VALUES ('francine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'fran'), (@last_id,'francie'), (@last_id,'francine'), (@last_id,'frannie'), (@last_id,'franny');

INSERT INTO fn_group (given, new) VALUES ('isadore',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'isadore'), (@last_id,'izzy');

INSERT INTO fn_group (given, new) VALUES ('gregory',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'greg'), (@last_id,'gregory');

INSERT INTO fn_group (given, new) VALUES ('timothy',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'tim'), (@last_id,'timmy'), (@last_id,'timo'), (@last_id,'timothy');

INSERT INTO fn_group (given, new) VALUES ('silence',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'liley'), (@last_id,'silence');

INSERT INTO fn_group (given, new) VALUES ('eustacia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'eustacia'), (@last_id,'stacia'), (@last_id,'stacy');

INSERT INTO fn_group (given, new) VALUES ('clarice',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'clarice');

INSERT INTO fn_group (given, new) VALUES ('carolyn',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'caddie'), (@last_id,'carol'), (@last_id,'carolyn'), (@last_id,'carrie'), (@last_id,'cassie'), (@last_id,'lena'), (@last_id,'lyn'), (@last_id,'lynn');

INSERT INTO fn_group (given, new) VALUES ('washington',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'wash'), (@last_id,'washington');

INSERT INTO fn_group (given, new) VALUES ('catherina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'casy'), (@last_id,'catherina'), (@last_id,'cathie'), (@last_id,'kate'), (@last_id,'kathie'), (@last_id,'kathleen'), (@last_id,'katie'), (@last_id,'katrine'), (@last_id,'kit'), (@last_id,'kitty');

INSERT INTO fn_group (given, new) VALUES ('sandra',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'sandra');

INSERT INTO fn_group (given, new) VALUES ('elvira',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'elvie'), (@last_id,'elvira'), (@last_id,'evie');

INSERT INTO fn_group (given, new) VALUES ('lois',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lois');

INSERT INTO fn_group (given, new) VALUES ('wilda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'wilda'), (@last_id,'willie');

INSERT INTO fn_group (given, new) VALUES ('elmira',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ellie'), (@last_id,'elly'), (@last_id,'elmira'), (@last_id,'mira');

INSERT INTO fn_group (given, new) VALUES ('katelin',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'kate'), (@last_id,'katelin'), (@last_id,'kay'), (@last_id,'kaye');

INSERT INTO fn_group (given, new) VALUES ('hubert',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bert'), (@last_id,'hub'), (@last_id,'hubert'), (@last_id,'hugh'), (@last_id,'hughie'), (@last_id,'hugo');

INSERT INTO fn_group (given, new) VALUES ('joyce',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'joy'), (@last_id,'joyce');

INSERT INTO fn_group (given, new) VALUES ('abel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ab'), (@last_id,'abe'), (@last_id,'abel'), (@last_id,'eb'), (@last_id,'ebbie');

INSERT INTO fn_group (given, new) VALUES ('cyrus',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cy'), (@last_id,'cyrus'), (@last_id,'si');

INSERT INTO fn_group (given, new) VALUES ('manoah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'manoah'), (@last_id,'noah');

INSERT INTO fn_group (given, new) VALUES ('celinda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'celinda'), (@last_id,'linda'), (@last_id,'lindy'), (@last_id,'lynn');

INSERT INTO fn_group (given, new) VALUES ('cecelia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cecelia'), (@last_id,'cele'), (@last_id,'celia');

INSERT INTO fn_group (given, new) VALUES ('kathleen',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'kathleen');

INSERT INTO fn_group (given, new) VALUES ('marcius',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'marcellus'), (@last_id,'marcius');

INSERT INTO fn_group (given, new) VALUES ('philadelphia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'philadelphia');

INSERT INTO fn_group (given, new) VALUES ('jannett',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jannett'), (@last_id,'nettie');

INSERT INTO fn_group (given, new) VALUES ('anderson',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ander'), (@last_id,'anderson'), (@last_id,'andy'), (@last_id,'sonny');

INSERT INTO fn_group (given, new) VALUES ('joseph',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jody'), (@last_id,'joe'), (@last_id,'joey'), (@last_id,'jos'), (@last_id,'joseph');

INSERT INTO fn_group (given, new) VALUES ('chauncey',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'chan'), (@last_id,'chance'), (@last_id,'chauncey');

INSERT INTO fn_group (given, new) VALUES ('aurelia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'aurelia'), (@last_id,'aurilla'), (@last_id,'ora'), (@last_id,'orilla'), (@last_id,'ree');

INSERT INTO fn_group (given, new) VALUES ('annette',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'annette');

INSERT INTO fn_group (given, new) VALUES ('erasmus',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'erasmus'), (@last_id,'rasmus');

INSERT INTO fn_group (given, new) VALUES ('isobel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bel'), (@last_id,'bella'), (@last_id,'isa'), (@last_id,'isobel'), (@last_id,'sibella');

INSERT INTO fn_group (given, new) VALUES ('adam',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ad'), (@last_id,'adam'), (@last_id,'ade');

INSERT INTO fn_group (given, new) VALUES ('patricia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'paddy'), (@last_id,'pat'), (@last_id,'pate'), (@last_id,'patricia'), (@last_id,'patrick'), (@last_id,'patsy'), (@last_id,'patty'), (@last_id,'perry'), (@last_id,'pete'), (@last_id,'peter'), (@last_id,'rick'), (@last_id,'tricia'), (@last_id,'trish'), (@last_id,'trisha'), (@last_id,'trixie');

INSERT INTO fn_group (given, new) VALUES ('augustus',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'augustus');

INSERT INTO fn_group (given, new) VALUES ('tamarra',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'tamarra'), (@last_id,'tammy');

INSERT INTO fn_group (given, new) VALUES ('mathilda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mat'), (@last_id,'mathilda'), (@last_id,'mattie'), (@last_id,'matty'), (@last_id,'maud'), (@last_id,'pat'), (@last_id,'patty'), (@last_id,'tilda'), (@last_id,'tillie');

INSERT INTO fn_group (given, new) VALUES ('roxanne',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ann'), (@last_id,'rose'), (@last_id,'roxanna'), (@last_id,'roxanne'), (@last_id,'roxie');

INSERT INTO fn_group (given, new) VALUES ('adrian',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'adrian'), (@last_id,'hadrian');

INSERT INTO fn_group (given, new) VALUES ('keziah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'keziah'), (@last_id,'kezzie'), (@last_id,'kezzy'), (@last_id,'kid'), (@last_id,'kizza'), (@last_id,'kizzie'), (@last_id,'kizzy');

INSERT INTO fn_group (given, new) VALUES ('leonardo',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lee'), (@last_id,'len'), (@last_id,'lenny'), (@last_id,'leo'), (@last_id,'leon'), (@last_id,'leonardo');

INSERT INTO fn_group (given, new) VALUES ('melanie',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'melanie'), (@last_id,'mellie');

INSERT INTO fn_group (given, new) VALUES ('lydia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lidia'), (@last_id,'lydia');

INSERT INTO fn_group (given, new) VALUES ('rhodella',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'della'), (@last_id,'rhodella');

INSERT INTO fn_group (given, new) VALUES ('josiah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'joe'), (@last_id,'josiah'), (@last_id,'si');

INSERT INTO fn_group (given, new) VALUES ('esther',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'essie'), (@last_id,'essy'), (@last_id,'esther'), (@last_id,'hester'), (@last_id,'hetty');

INSERT INTO fn_group (given, new) VALUES ('reuben',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'reuben'), (@last_id,'rube'), (@last_id,'ruby');

INSERT INTO fn_group (given, new) VALUES ('clifton',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'clifton');

INSERT INTO fn_group (given, new) VALUES ('louvinia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'louvinia'), (@last_id,'vina'), (@last_id,'viney'), (@last_id,'vonnie');

INSERT INTO fn_group (given, new) VALUES ('mackenzie',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'kenzy'), (@last_id,'mac'), (@last_id,'mack'), (@last_id,'mackenzie'), (@last_id,'mc');

INSERT INTO fn_group (given, new) VALUES ('laverne',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'laverne'), (@last_id,'verna'), (@last_id,'vernon');

INSERT INTO fn_group (given, new) VALUES ('crystal',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'chris'), (@last_id,'crys'), (@last_id,'crystal'), (@last_id,'tal');

INSERT INTO fn_group (given, new) VALUES ('wilhelmina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bill'), (@last_id,'billie'), (@last_id,'billy'), (@last_id,'bud'), (@last_id,'helmie'), (@last_id,'liam'), (@last_id,'mina'), (@last_id,'minnie'), (@last_id,'wilhelm'), (@last_id,'wilhelmina'), (@last_id,'will'), (@last_id,'william'), (@last_id,'willie'), (@last_id,'willy'), (@last_id,'wilma');

INSERT INTO fn_group (given, new) VALUES ('percival',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'perce'), (@last_id,'percival'), (@last_id,'percy');

INSERT INTO fn_group (given, new) VALUES ('jerita',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jerita'), (@last_id,'rita');

INSERT INTO fn_group (given, new) VALUES ('aquilla',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'aquilla'), (@last_id,'quillie');

INSERT INTO fn_group (given, new) VALUES ('seymore',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'morey'), (@last_id,'see'), (@last_id,'seymore');

INSERT INTO fn_group (given, new) VALUES ('beatrice',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bea'), (@last_id,'beatrice'), (@last_id,'trisha'), (@last_id,'trix'), (@last_id,'trixie');

INSERT INTO fn_group (given, new) VALUES ('ezekiel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ezekiel'), (@last_id,'zeke');

INSERT INTO fn_group (given, new) VALUES ('kathryn',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'kathryn'), (@last_id,'kathy');

INSERT INTO fn_group (given, new) VALUES ('bernadette',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'bernadette'), (@last_id,'bunny');

INSERT INTO fn_group (given, new) VALUES ('elisha',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'eli'), (@last_id,'elisha'), (@last_id,'ellis'), (@last_id,'lish');

INSERT INTO fn_group (given, new) VALUES ('aristotle',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'aristotle'), (@last_id,'telly');

INSERT INTO fn_group (given, new) VALUES ('octavius',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'octavius'), (@last_id,'tavy');

INSERT INTO fn_group (given, new) VALUES ('rupert',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'rupert');

INSERT INTO fn_group (given, new) VALUES ('adelaida',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'adelaida'), (@last_id,'ida');

INSERT INTO fn_group (given, new) VALUES ('adelaide',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'adelaide');

INSERT INTO fn_group (given, new) VALUES ('angelica',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'angelica');

INSERT INTO fn_group (given, new) VALUES ('cathleen',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cassie'), (@last_id,'cat'), (@last_id,'catherine'), (@last_id,'cathleen'), (@last_id,'cathy'), (@last_id,'kathy'), (@last_id,'katie'), (@last_id,'katy'), (@last_id,'kay'), (@last_id,'kit'), (@last_id,'kittie'), (@last_id,'lena'), (@last_id,'trina');

INSERT INTO fn_group (given, new) VALUES ('suzanne',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'suzanne');

INSERT INTO fn_group (given, new) VALUES ('heather',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'heather'), (@last_id,'hetty');

INSERT INTO fn_group (given, new) VALUES ('joan',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'joan');

INSERT INTO fn_group (given, new) VALUES ('mortimer',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mort'), (@last_id,'mortimer');

INSERT INTO fn_group (given, new) VALUES ('abigail',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'abbie'), (@last_id,'abby'), (@last_id,'abigail'), (@last_id,'gail');

INSERT INTO fn_group (given, new) VALUES ('morag',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'cera'), (@last_id,'marion'), (@last_id,'morag'), (@last_id,'sadie'), (@last_id,'sal'), (@last_id,'sallie'), (@last_id,'sally'), (@last_id,'sara'), (@last_id,'sarah'), (@last_id,'sukie');

INSERT INTO fn_group (given, new) VALUES ('derrick',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'derrick'), (@last_id,'eric'), (@last_id,'erica'), (@last_id,'rick'), (@last_id,'ricky');

INSERT INTO fn_group (given, new) VALUES ('malachi',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mal'), (@last_id,'malachi'), (@last_id,'mally');

INSERT INTO fn_group (given, new) VALUES ('ellen',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ellen');

INSERT INTO fn_group (given, new) VALUES ('elaine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'elaine');

INSERT INTO fn_group (given, new) VALUES ('delbert',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'delbert');

INSERT INTO fn_group (given, new) VALUES ('dolores',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'dee'), (@last_id,'dolores');

INSERT INTO fn_group (given, new) VALUES ('jeptha',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jep'), (@last_id,'jeptha');

INSERT INTO fn_group (given, new) VALUES ('wallace',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'wallace'), (@last_id,'wallie'), (@last_id,'wally');

INSERT INTO fn_group (given, new) VALUES ('june',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'june');

INSERT INTO fn_group (given, new) VALUES ('regina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'gina'), (@last_id,'ray'), (@last_id,'reggie'), (@last_id,'regina');

INSERT INTO fn_group (given, new) VALUES ('lucia',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lucia'), (@last_id,'lucy');

INSERT INTO fn_group (given, new) VALUES ('william',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'william');

INSERT INTO fn_group (given, new) VALUES ('margaret',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'margaret');

INSERT INTO fn_group (given, new) VALUES ('maureen',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'maureen');

INSERT INTO fn_group (given, new) VALUES ('miranda',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'mandy'), (@last_id,'mari'), (@last_id,'mindy'), (@last_id,'mira'), (@last_id,'miranda'), (@last_id,'randy');

INSERT INTO fn_group (given, new) VALUES ('frederica',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'frederica');

INSERT INTO fn_group (given, new) VALUES ('rosalyn',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'linda'), (@last_id,'rosa'), (@last_id,'rosalinda'), (@last_id,'rosalyn'), (@last_id,'rose'), (@last_id,'roz');

INSERT INTO fn_group (given, new) VALUES ('natalie',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'natalie'), (@last_id,'nattie'), (@last_id,'natty'), (@last_id,'nettie'), (@last_id,'tally');

INSERT INTO fn_group (given, new) VALUES ('edith',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ada'), (@last_id,'dicey'), (@last_id,'edie'), (@last_id,'edith');

INSERT INTO fn_group (given, new) VALUES ('martha',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'marnie'), (@last_id,'mart'), (@last_id,'martha'), (@last_id,'marty'), (@last_id,'mat'), (@last_id,'mattie'), (@last_id,'matty'), (@last_id,'meg'), (@last_id,'molly'), (@last_id,'pat'), (@last_id,'patsy'), (@last_id,'pattie'), (@last_id,'patty');

INSERT INTO fn_group (given, new) VALUES ('annaka',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ann'), (@last_id,'annaka'), (@last_id,'anne'), (@last_id,'annie'), (@last_id,'nancy'), (@last_id,'niki');

INSERT INTO fn_group (given, new) VALUES ('geraldine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'deannie'), (@last_id,'dina'), (@last_id,'gerald'), (@last_id,'geraldine'), (@last_id,'gerri'), (@last_id,'gerrie'), (@last_id,'gerry'), (@last_id,'jerry');

INSERT INTO fn_group (given, new) VALUES ('kristel',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'kris'), (@last_id,'kristel');

INSERT INTO fn_group (given, new) VALUES ('raphael',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'raff'), (@last_id,'rafi'), (@last_id,'ralph'), (@last_id,'raphael');

INSERT INTO fn_group (given, new) VALUES ('jebadiah',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'jeb'), (@last_id,'jebadiah');

INSERT INTO fn_group (given, new) VALUES ('magdalene',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'lena'), (@last_id,'magdalene'), (@last_id,'maud');

INSERT INTO fn_group (given, new) VALUES ('lafayette',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'fate'), (@last_id,'fayette'), (@last_id,'lafayette');

INSERT INTO fn_group (given, new) VALUES ('nelson',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'nels'), (@last_id,'nelson');

INSERT INTO fn_group (given, new) VALUES ('clemintine',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'clement'), (@last_id,'clemintine');

INSERT INTO fn_group (given, new) VALUES ('wilma',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'wilma');

INSERT INTO fn_group (given, new) VALUES ('edwina',0);
SET @last_id:=LAST_INSERT_ID();
INSERT INTO fn_group_name (fn_group_id, name) VALUES
	(@last_id,'ed'), (@last_id,'eddie'), (@last_id,'eddy'), (@last_id,'edwin'), (@last_id,'edwina'), (@last_id,'ned'), (@last_id,'neddie'), (@last_id,'ted'), (@last_id,'teddy'), (@last_id,'win');

