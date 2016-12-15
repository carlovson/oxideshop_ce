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

namespace OxidEsales\EshopCommunity\Setup\Controller;

use OxidEsales\EshopCommunity\Core\SystemRequirements;

/**
 * Class ModuleStateMapGenerator.
 *
 * Accepts SystemRequirementsInfo as primary source of data and converts it to be compatible with setup's view
 * component which displays the system requirements (Used in Controller).
 *
 * It also accepts the following custom functions to help and deal with:
 *
 *   - ModuleStateHtmlClass converter to map module state integer value to custom HTML class strings;
 *   - ModuleNameTranslate to translate module id to it's full name/title;
 *   - ModuleGroupNameTranslate to translate group module id to it's full name/title;
 */
class ModuleStateMapGenerator
{
    const MODULE_ID_KEY = 'module';
    const MODULE_STATE_KEY = 'state';
    const MODULE_NAME_KEY = 'modulename';
    const MODULE_STATE_HTML_CLASS_KEY = 'class';

    /** @var array */
    private $systemRequirementsInfo = [];

    /** @var \Closure */
    private $moduleStateHtmlClassConvertFunction = null;

    /** @var \Closure */
    private $moduleNameTranslateFunction = null;

    /** @var \Closure */
    private $moduleGroupNameTranslateFunction = null;

    /**
     * ModuleStateMapGenerator constructor.
     * @param array $systemRequirementsInfo
     */
    public function __construct($systemRequirementsInfo = [])
    {
        $this->systemRequirementsInfo = $systemRequirementsInfo;
    }

    /**
     * Returns module state map with all applied filters.
     *
     * In case a filter is not set it will be just skipped.
     *
     * @return array
     */
    public function getModuleStateMap()
    {
        $moduleStateMap = $this->convertFromSystemRequirementsInfo();
        $moduleStateMap = $this->applyModuleStateHtmlClassConvertFunction($moduleStateMap);
        $moduleStateMap = $this->applyModuleNameTranslateFunction($moduleStateMap);
        $moduleStateMap = $this->applyModuleGroupNameTranslateFunction($moduleStateMap);

        return $moduleStateMap;
    }

    /**
     * @return array
     */
    private function convertFromSystemRequirementsInfo()
    {
        $moduleStateMap = [];

        $iteration = SystemRequirements::iterateThroughSystemRequirementsInfo($this->systemRequirementsInfo);
        foreach ($iteration as list($groupId, $moduleId, $moduleState)) {
            $moduleStateMap[$groupId][] = [
                self::MODULE_ID_KEY => $moduleId,
                self::MODULE_STATE_KEY => $moduleState,
            ];
        }

        return $moduleStateMap;
    }

    /**
     * @param array $moduleStateMap
     * @return array
     */
    private function applyModuleStateHtmlClassConvertFunction($moduleStateMap)
    {
        return $this->applyModuleStateMapFilterFunction(
            $moduleStateMap,
            $this->moduleStateHtmlClassConvertFunction,
            function ($moduleData, $convertFunction) {
                $moduleState = $moduleData[self::MODULE_STATE_KEY];
                $moduleData[self::MODULE_STATE_HTML_CLASS_KEY] = $convertFunction($moduleState);

                return $moduleData;
            }
        );
    }

    /**
     * @param array $moduleStateMap
     * @return array
     */
    private function applyModuleNameTranslateFunction($moduleStateMap)
    {
        return $this->applyModuleStateMapFilterFunction(
            $moduleStateMap,
            $this->moduleNameTranslateFunction,
            function ($moduleData, $translateFunction) {
                $moduleId = $moduleData[self::MODULE_ID_KEY];
                $moduleData[self::MODULE_NAME_KEY] = $translateFunction($moduleId);

                return $moduleData;
            }
        );
    }

    /**
     * @param array $moduleStateMap
     * @return array
     */
    private function applyModuleGroupNameTranslateFunction($moduleStateMap)
    {
        $moduleGroupNameTranslateFilterFunction = $this->moduleGroupNameTranslateFunction;

        if (!$moduleGroupNameTranslateFilterFunction) {
            return $moduleStateMap;
        }

        $translatedModuleStateMap = [];

        foreach ($this->iterateThroughModuleStateMapByGroup($moduleStateMap) as list($groupId, $modules)) {
            $groupName = $moduleGroupNameTranslateFilterFunction($groupId);
            $translatedModuleStateMap[$groupName] = $modules;
        }

        return $translatedModuleStateMap;
    }

    /**
     * Sets function which knows how to convert given module state to Html class.
     *
     * Single argument is given to the provided function as the state of module.
     *
     * @param \Closure $function
     * @throws \Exception
     */
    public function setModuleStateHtmlClassConvertFunction($function)
    {
        $this->validateClosure($function);
        $this->moduleStateHtmlClassConvertFunction = $function;
    }

    /**
     * Sets function which defines how module name should be translated.
     *
     * Single argument is given to the provided function as the module id.
     *
     * @param \Closure $function
     * @throws \Exception
     */
    public function setModuleNameTranslateFunction($function)
    {
        $this->validateClosure($function);
        $this->moduleNameTranslateFunction = $function;
    }

    /**
     * Sets function which defines how module group name should be translated.
     *
     * Single argument is given to the provided function as the module group id.
     *
     * @param \Closure $function
     * @throws \Exception
     */
    public function setModuleGroupNameTranslateFunction($function)
    {
        $this->validateClosure($function);
        $this->moduleGroupNameTranslateFunction = $function;
    }

    /**
     * @param array $moduleStateMap
     * @return \Generator
     */
    private function iterateThroughModuleStateMapByGroup($moduleStateMap)
    {
        foreach ($moduleStateMap as $groupId => $modules) {
            yield [$groupId, $modules];
        }
    }

    /**
     * @param array $moduleStateMap
     * @return \Generator
     */
    private function iterateThroughModuleStateMap($moduleStateMap)
    {
        foreach ($this->iterateThroughModuleStateMapByGroup($moduleStateMap) as list($groupId, $modules)) {
            foreach ($modules as $moduleIndex => $moduleData) {
                yield [$groupId, $moduleIndex, $moduleData];
            }
        }
    }

    /**
     * @param array    $moduleStateMap
     * @param \Closure $helpFunction                 Help function which will be passed to moduleStateMapUpdateFunction
     *                                               as 2nd argument.
     * @param \Closure $moduleStateMapUpdateFunction Function which will be used to modify contents of module state map.
     * @return array
     */
    private function applyModuleStateMapFilterFunction($moduleStateMap, $helpFunction, $moduleStateMapUpdateFunction)
    {
        if (!$helpFunction) {
            return $moduleStateMap;
        }

        foreach ($this->iterateThroughModuleStateMap($moduleStateMap) as list($groupId, $moduleIndex, $moduleData)) {
            $moduleStateMap[$groupId][$moduleIndex] = $moduleStateMapUpdateFunction($moduleData, $helpFunction);
        }

        return $moduleStateMap;
    }

    /**
     * @param \Closure $object
     * @throws \Exception
     */
    private function validateClosure($object)
    {
        if (!$object instanceof \Closure) {
            throw new \Exception('Given argument must be an instance of Closure.');
        }
    }
}
