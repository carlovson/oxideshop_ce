<?php
/**
 * This file is part of OXID eShop Community Edition.
 *
 * OXID eShop Community Edition is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eShop Community Edition is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2016
 * @version   OXID eShop CE
 */

/**
 * Testing oxseoencodervendor class
 */
class Unit_Models_oxSeoEncoderVendorTest extends OxidTestCase
{
    /**
     * Initialize the fixture.
     */
    protected function setUp()
    {
        parent::setUp();

        oxTestModules::addFunction("oxutils", "seoIsActive", "{return true;}");
    }

    /**
     * Tear down the fixture.
     */
    protected function tearDown()
    {
        modDB::getInstance()->cleanup();
        oxDb::getDb()->execute('delete from oxseo where oxtype != "static"');
        oxDb::getDb()->execute('delete from oxobject2seodata');
        oxDb::getDb()->execute('delete from oxseohistory');

        $this->cleanUpTable('oxcategories');

        parent::tearDown();
    }

    /**
     * oxSeoEncoderManufacturer::_getAltUri() test case
     */
    public function testGetAltUriTag()
    {
        oxTestModules::addFunction("oxVendor", "loadInLang", "{ return true; }");

        $oEncoder = $this->getMock("oxSeoEncoderVendor", array("getVendorUri"));
        $oEncoder->expects($this->once())->method('getVendorUri')->will($this->returnValue("vendorUri"));

        $this->assertEquals("vendorUri", $oEncoder->UNITgetAltUri('1126', 0));
    }

    public function testGetVendorUrlExistingVendor()
    {
        oxTestModules::addFunction("oxUtilsServer", "getServerVar", "{ \$aArgs = func_get_args(); if ( \$aArgs[0] === 'HTTP_HOST' ) { return '" . $this->getConfig()->getShopUrl() . "'; } elseif ( \$aArgs[0] === 'SCRIPT_NAME' ) { return ''; } else { return \$_SERVER[\$aArgs[0]]; } }");

        $sVndId = 'd2e44d9b32fd2c224.65443178';
        $sUrl = 'Nach-Lieferant/Hersteller-2/';

        $oVendor = oxNew('oxVendor');
        $oVendor->load($sVndId);

        $oEncoder = oxNew('oxSeoEncoderVendor');
        $this->assertEquals($this->getConfig()->getShopUrl() . $sUrl, $oEncoder->getVendorUrl($oVendor));
    }

    public function testGetVendorUrlExistingVendorEng()
    {
        oxTestModules::addFunction("oxUtilsServer", "getServerVar", "{ \$aArgs = func_get_args(); if ( \$aArgs[0] === 'HTTP_HOST' ) { return '" . $this->getConfig()->getShopUrl() . "'; } elseif ( \$aArgs[0] === 'SCRIPT_NAME' ) { return ''; } else { return \$_SERVER[\$aArgs[0]]; } }");
        oxTestModules::addFunction('oxVendor', 'resetRootVendor', '{ self::$_aRootVendor = array() ; }');
        $oVendor = oxNew('oxVendor');
        $oVendor->resetRootVendor();

        $sVndId = 'd2e44d9b32fd2c224.65443178';
        $sUrl = 'en/By-Distributor/Manufacturer-2/';

        $oVendor = oxNew('oxVendor');
        $oVendor->loadInLang(1, $sVndId);

        $oEncoder = oxNew('oxSeoEncoderVendor');
        $this->assertEquals($this->getConfig()->getShopUrl() . $sUrl, $oEncoder->getVendorUrl($oVendor));
    }

    public function testGetVendorUrlExistingVendorWithLangParam()
    {
        oxTestModules::addFunction("oxUtilsServer", "getServerVar", "{ \$aArgs = func_get_args(); if ( \$aArgs[0] === 'HTTP_HOST' ) { return '" . $this->getConfig()->getShopUrl() . "'; } elseif ( \$aArgs[0] === 'SCRIPT_NAME' ) { return ''; } else { return \$_SERVER[\$aArgs[0]]; } }");

        $sVndId = 'd2e44d9b32fd2c224.65443178';
        $sUrl = 'Nach-Lieferant/Hersteller-2/';

        $oVendor = oxNew('oxVendor');
        $oVendor->loadInLang(1, $sVndId);

        $oEncoder = oxNew('oxSeoEncoderVendor');
        $this->assertEquals($this->getConfig()->getShopUrl() . $sUrl, $oEncoder->getVendorUrl($oVendor, 0));
    }

    public function testGetVendorUrlExistingVendorEngWithLangParam()
    {
        oxTestModules::addFunction("oxUtilsServer", "getServerVar", "{ \$aArgs = func_get_args(); if ( \$aArgs[0] === 'HTTP_HOST' ) { return '" . $this->getConfig()->getShopUrl() . "'; } elseif ( \$aArgs[0] === 'SCRIPT_NAME' ) { return ''; } else { return \$_SERVER[\$aArgs[0]]; } }");
        oxTestModules::addFunction('oxVendor', 'resetRootVendor', '{ self::$_aRootVendor = array() ; }');
        $oVendor = oxNew('oxVendor');
        $oVendor->resetRootVendor();

        $sVndId = 'd2e44d9b32fd2c224.65443178';
        $sUrl = 'en/By-Distributor/Manufacturer-2/';

        $oVendor = oxNew('oxVendor');
        $oVendor->loadInLang(0, $sVndId);

        $oEncoder = oxNew('oxSeoEncoderVendor');
        $this->assertEquals($this->getConfig()->getShopUrl() . $sUrl, $oEncoder->getVendorUrl($oVendor, 1));
    }

    /**
     * Testing vendor uri getter
     */
    public function testGetVendorUriExistingVendor()
    {
        $oVendor = oxNew('oxVendor');
        $oVendor->setId('xxx');

        $oEncoder = $this->getMock('oxSeoEncoderVendor', array('_loadFromDb', '_prepareTitle'));
        $oEncoder->expects($this->once())->method('_loadFromDb')->with($this->equalTo('oxvendor'), $this->equalTo('xxx'), $this->equalTo($oVendor->getLanguage()))->will($this->returnValue('seourl'));
        $oEncoder->expects($this->never())->method('_prepareTitle');
        $oEncoder->expects($this->never())->method('_getUniqueSeoUrl');
        $oEncoder->expects($this->never())->method('_saveToDb');

        $sUrl = 'seourl';
        $sSeoUrl = $oEncoder->getVendorUri($oVendor);
        $this->assertEquals($sUrl, $sSeoUrl);
    }

    public function testGetVendorUriRootVendor()
    {
        $oVendor = oxNew('oxVendor');
        $oVendor->setId('root');
        $oVendor->oxvendor__oxtitle = new oxField('root', oxField::T_RAW);

        $oEncoder = $this->getMock('oxSeoEncoderVendor', array('_saveToDb'));
        $oEncoder->expects($this->once())->method('_saveToDb')->with($this->equalTo('oxvendor'), $this->equalTo('root'), $this->equalTo($oVendor->getStdLink()), $this->equalTo('root/'), $this->equalTo($oVendor->getLanguage()));

        $sUrl = 'root/';
        $sSeoUrl = $oEncoder->getVendorUri($oVendor);
        $this->assertEquals($sUrl, $sSeoUrl);
    }

    public function testGetVendorUriRootVendorSecondLanguage()
    {
        $oVendor = oxNew('oxVendor');
        $oVendor->setId('root');
        $oVendor->setLanguage(1);
        $oVendor->oxvendor__oxtitle = new oxField('root', oxField::T_RAW);

        $oEncoder = $this->getMock('oxSeoEncoderVendor', array('_saveToDb'));
        $oEncoder->expects($this->once())->method('_saveToDb')->with($this->equalTo('oxvendor'), $this->equalTo('root'), $this->equalTo($oVendor->getBaseStdLink(1)), $this->equalTo('en/root/'), $this->equalTo($oVendor->getLanguage()));

        $sUrl = 'en/root/';
        $sSeoUrl = $oEncoder->getVendorUri($oVendor);
        $this->assertEquals($sUrl, $sSeoUrl);
    }

    public function testGetVendorUriNewVendor()
    {
        $oVendor = oxNew('oxVendor');
        $oVendor->setLanguage(1);
        $oVendor->setId('xxx');
        $oVendor->oxvendor__oxtitle = new oxField('xxx', oxField::T_RAW);

        $oEncoder = $this->getMock('oxSeoEncoderVendor', array('_loadFromDb', '_saveToDb'));
        $oEncoder->expects($this->exactly(2))->method('_loadFromDb')->will($this->returnValue(false));

        $sUrl = 'en/By-Distributor/xxx/';
        $sSeoUrl = $oEncoder->getVendorUri($oVendor);

        $this->assertEquals($sUrl, $sSeoUrl);
    }

    /**
     * Testing object url getter
     */
    public function testGetVendorPageUrl()
    {
        oxTestModules::addFunction("oxUtilsServer", "getServerVar", "{ \$aArgs = func_get_args(); if ( \$aArgs[0] === 'HTTP_HOST' ) { return '" . $this->getConfig()->getShopUrl() . "'; } elseif ( \$aArgs[0] === 'SCRIPT_NAME' ) { return ''; } else { return \$_SERVER[\$aArgs[0]]; } }");

        $sVndId = 'd2e44d9b32fd2c224.65443178';
        $sUrl = 'en/By-Distributor/Manufacturer-2/101/';

        $oVendor = oxNew('oxVendor');
        $oVendor->loadInLang(1, $sVndId);

        $oEncoder = oxNew('oxSeoEncoderVendor');
        $this->assertEquals($this->getConfig()->getShopUrl() . $sUrl, $oEncoder->getVendorPageUrl($oVendor, 100));
    }

    public function testGetVendorPageUrlWithLangParam()
    {
        oxTestModules::addFunction("oxUtilsServer", "getServerVar", "{ \$aArgs = func_get_args(); if ( \$aArgs[0] === 'HTTP_HOST' ) { return '" . $this->getConfig()->getShopUrl() . "'; } elseif ( \$aArgs[0] === 'SCRIPT_NAME' ) { return ''; } else { return \$_SERVER[\$aArgs[0]]; } }");

        $sVndId = 'd2e44d9b32fd2c224.65443178';
        $sUrl = 'en/By-Distributor/Manufacturer-2/101/';

        $oVendor = oxNew('oxVendor');
        $oVendor->loadInLang(0, $sVndId);

        $oEncoder = oxNew('oxSeoEncoderVendor');
        $this->assertEquals($this->getConfig()->getShopUrl() . $sUrl, $oEncoder->getVendorPageUrl($oVendor, 100, 1));
    }

    public function testGetVendorUrl()
    {
        $oVendor = $this->getMock('oxcategory', array('getLanguage'));
        $oVendor->expects($this->once())->method('getLanguage')->will($this->returnValue(0));

        $oEncoder = $this->getMock('oxSeoEncoderVendor', array('_getFullUrl', 'getVendorUri'));
        $oEncoder->expects($this->once())->method('_getFullUrl')->will($this->returnValue('seovndurl'));
        $oEncoder->expects($this->once())->method('getVendorUri');

        $this->assertEquals('seovndurl', $oEncoder->getVendorUrl($oVendor));
    }

    public function testGetVendorUriExistingVendorWithLangParam()
    {
        $oVendor = oxNew('oxVendor');
        $oVendor->setLanguage(1);
        $oVendor->setId('xxx');

        $oEncoder = $this->getMock('oxSeoEncoderVendor', array('_loadFromDb', '_prepareTitle'));
        $oEncoder->expects($this->once())->method('_loadFromDb')->with($this->equalTo('oxvendor'), $this->equalTo('xxx'), $this->equalTo(0))->will($this->returnValue('seourl'));
        $oEncoder->expects($this->never())->method('_prepareTitle');
        $oEncoder->expects($this->never())->method('_getUniqueSeoUrl');
        $oEncoder->expects($this->never())->method('_saveToDb');

        $sUrl = 'seourl';
        $sSeoUrl = $oEncoder->getVendorUri($oVendor, 0);
        $this->assertEquals($sUrl, $sSeoUrl);
    }

    public function testGetVendorUriRootVendorWithLangParam()
    {
        $oVendor = oxNew('oxVendor');
        $oVendor->setId('root');
        $oVendor->oxvendor__oxtitle = new oxField('root', oxField::T_RAW);

        $oEncoder = $this->getMock('oxSeoEncoderVendor', array('_saveToDb'));
        $oEncoder->expects($this->once())->method('_saveToDb')->with($this->equalTo('oxvendor'), $this->equalTo('root'), $this->equalTo($oVendor->getBaseStdLink(1)), $this->equalTo('en/By-Distributor/'), $this->equalTo(1));

        $sUrl = 'en/By-Distributor/';
        $sSeoUrl = $oEncoder->getVendorUri($oVendor, 1);
        $this->assertEquals($sUrl, $sSeoUrl);
    }

    public function testonDeleteVendor()
    {
        $sShopId = $this->getConfig()->getBaseShopId();
        $oDb = oxDb::getDb();
        $sQ = "insert into oxseo
                   ( oxobjectid, oxident, oxshopid, oxlang, oxstdurl, oxseourl, oxtype, oxfixed, oxexpired, oxparams )
               values
                   ( 'oid', '132', '{$sShopId}', '0', '', '', 'oxvendor', '0', '0', '' )";
        $oDb->execute($sQ);

        $sQ = "insert into oxobject2seodata ( oxobjectid, oxshopid, oxlang ) values ( 'oid', '{$sShopId}', '0' )";
        $oDb->execute($sQ);

        $this->assertTrue((bool) $oDb->getOne("select 1 from oxseo where oxobjectid = 'oid'"));
        $this->assertTrue((bool) $oDb->getOne("select 1 from oxobject2seodata where oxobjectid = 'oid'"));

        $oObj = oxNew('oxbase');
        $oObj->setId('oid');

        $oEncoder = oxNew('oxSeoEncoderVendor');
        $oEncoder->onDeleteVendor($oObj);

        $this->assertFalse((bool) $oDb->getOne("select 1 from oxseo where oxobjectid = 'oid'"));
        $this->assertFalse((bool) $oDb->getOne("select 1 from oxobject2seodata where oxobjectid = 'oid'"));

    }
}
