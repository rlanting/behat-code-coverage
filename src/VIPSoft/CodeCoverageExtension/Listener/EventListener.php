<?php
/**
 * Event Listener
 *
 * @copyright 2013 Anthon Pang
 * @license BSD-2-Clause
 */

namespace VIPSoft\CodeCoverageExtension\Listener;

use Behat\Behat\Tester\Event\AbstractScenarioTested;
use Behat\Behat\Tester\Event\ExampleTested;
use Behat\Behat\Tester\Event\FeatureTested;
use Behat\Behat\Tester\Event\ScenarioTested;
use Behat\Testwork\Tester\Event\ExerciseCompleted;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use VIPSoft\CodeCoverageExtension\Service\ReportService;

/**
 * Event listener
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class EventListener implements EventSubscriberInterface
{
    /**
     * @var \PHP_CodeCoverage
     */
    private $coverage;

    /**
     * @var \VIPSoft\CodeCoverageExtension\Service\ReportService
     */
    private $reportService;

    /**
     * Constructor
     *
     * @param \PHP_CodeCoverage                                    $coverage
     * @param \VIPSoft\CodeCoverageExtension\Service\ReportService $reportService
     */
    public function __construct(\PHP_CodeCoverage $coverage, ReportService $reportService)
    {
        $this->coverage      = $coverage;
        $this->reportService = $reportService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            ExerciseCompleted::BEFORE => 'beforeExercise',
            ScenarioTested::BEFORE => 'beforeScenario',
            ExampleTested::BEFORE  => 'beforeScenario',
            ScenarioTested::AFTER  => 'afterScenario',
            ExampleTested::AFTER   => 'afterScenario',
            ExerciseCompleted::AFTER => 'afterExercise',
        );
    }

    /**
     * Before Exercise hook
     *
     * @param \Behat\Testwork\Tester\Event\ExerciseCompleted $event
     */
    public function beforeExercise(ExerciseCompleted $event)
    {
        $this->coverage->clear();
    }

    /**
     * Before Scenario/Outline Example hook
     *
     * @param \Behat\Behat\Tester\Event\AbstractScenarioTested $event
     */
    public function beforeScenario(AbstractScenarioTested $event)
    {
        $node = $event->getScenario();
        $id   = $event->getFeature()->getFile() . ':' . $node->getLine();

        $this->coverage->start($id);
    }

    /**
     * After Scenario/Outline Example hook
     *
     * @param \Behat\Behat\Tester\Event\AbstractScenarioTested $event
     */
    public function afterScenario(AbstractScenarioTested $event)
    {
        $this->coverage->stop();
    }

    /**
     * After Exercise hook
     *
     * @param \Behat\Testwork\Tester\Event\ExerciseCompleted $event
     */
    public function afterExercise(ExerciseCompleted $event)
    {
        $this->reportService->generateReport($this->coverage);
    }
}
