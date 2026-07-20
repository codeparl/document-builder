<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines;

use Illuminate\Contracts\Container\Container;
use Illuminate\Pipeline\Pipeline;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentExecutionResult;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\DocumentPipeline;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
use UnnovateBrains\DocumentBuilder\Support\DocumentPipelineContext;
use UnnovateBrains\DocumentBuilder\Exceptions\PipelineException;
use UnnovateBrains\DocumentBuilder\Pipelines\Stages\LeaveContextStage;


/**
 * DocumentPipelineProcessor
 *
 * Executes the document generation lifecycle.
 *
 * Supports:
 *
 * 1. Full execution
 *
 *      EnterContext
 *          |
 *      ...
 *          |
 *      Output
 *
 *
 * 2. Pipeline continuation
 *
 *      MergeStage
 *          |
 *      OutputStage
 *          |
 *      LeaveContextStage
 *
 *
 * This is required for asynchronous chunk processing.
 */
final class DocumentPipelineProcessor implements DocumentPipeline
{


    /**
     * Ordered document lifecycle stages.
     *
     * @var array<class-string>
     */
    private array $stages = [

        Stages\EnterContextStage::class,

        Stages\ResolveContextStage::class,

        Stages\CompileDriverStage::class,

        Stages\ConfigureEngineStage::class,


        Stages\ResolveSourceStage::class,

        Stages\ChunkingStage::class,


        Stages\TransformStage::class,

        Stages\RenderTemplateStage::class,


        Stages\GenerateDocumentStage::class,


        Stages\MergeStage::class,


        Stages\OutputStage::class,


        Stages\LeaveContextStage::class,

    ];





    public function __construct(
        private readonly Container $container
    ) {}






    /**
     * Execute complete document workflow.
     */
    public function execute(
        ExecutionPlan $plan
    ): DocumentExecutionResult {


        try {


            $context =
                new DocumentPipelineContext(
                    $plan
                );



            $processed =
                $this->executeContext(
                    $context
                );



            $result =
                $processed->getResult();



            if (
                !$result instanceof DocumentExecutionResult
            ) {

                throw new PipelineException(
                    'Pipeline finished without result.'
                );
            }



            return $result;
        } catch (\Throwable $e) {


            throw PipelineException::stageFailed(
                'ExecutionPipeline',
                $e
            );
        }
    }







    /**
     * Execute full pipeline.
     */
    public function executeContext(
        DocumentPipelineContext $context
    ): DocumentPipelineContext {


        return $this->runPipeline(
            $context,
            $this->stages
        );
    }







    /**
     * Continue execution from a specific stage.
     *
     * Example:
     *
     * resume(
     *    $context,
     *    MergeStage::class
     * );
     *
     *
     * Executes:
     *
     * MergeStage
     * OutputStage
     * LeaveContextStage
     *
     */
    public function resume(
        DocumentPipelineContext $context,
        string $stage
    ): DocumentPipelineContext {


        $position =
            array_search(
                $stage,
                $this->stages,
                true
            );



        if ($position === false) {

            throw new \InvalidArgumentException(

                "Pipeline stage {$stage} does not exist."

            );
        }



        $remainingStages =
            array_slice(
                $this->stages,
                $position
            );



        return $this->runPipeline(
            $context,
            $remainingStages
        );
    }







    /**
     * Internal pipeline runner.
     */
    protected function runPipeline(
        DocumentPipelineContext $context,
        array $stages
    ): DocumentPipelineContext {


        try {


            return (new Pipeline($this->container))

                ->send($context)

                ->through($stages)

                ->thenReturn();
        } catch (\Throwable $e) {


            app(LeaveContextStage::class)

                ->handle(

                    $context,

                    fn($ctx) => $ctx

                );



            throw $e;
        }
    }
}
