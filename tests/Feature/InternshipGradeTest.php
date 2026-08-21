<?php

use App\Enums\UserRole;
use App\Models\Internship;
use App\Models\User;
use App\Policies\InternshipPolicy;

test('calculates the consolidated grade from the three component grades', function () {
    $internship = new Internship([
        'internship_type_weight' => 6,
        'report_weight' => 2,
        'presentation_weight' => 2,
        'evaluation_grade' => 5.5,
        'report_grade' => 1.8,
        'presentation_grade' => 2,
    ]);

    expect($internship->hasGradeWeightsConfigured())->toBeTrue()
        ->and($internship->calculateConsolidatedGrade())->toBe(9.3);
});

test('does not calculate a consolidated grade while a component is pending', function () {
    $internship = new Internship([
        'internship_type_weight' => 6,
        'report_weight' => 2,
        'presentation_weight' => 2,
        'evaluation_grade' => 5.5,
        'report_grade' => 1.8,
    ]);

    expect($internship->calculateConsolidatedGrade())->toBeNull();
});

test('requires the three weights to total ten points', function () {
    $internship = new Internship([
        'internship_type_weight' => 5,
        'report_weight' => 2,
        'presentation_weight' => 2,
    ]);

    expect($internship->hasGradeWeightsConfigured())->toBeFalse()
        ->and($internship->calculateConsolidatedGrade())->toBeNull();
});

test('only the assigned advisor can update report and presentation grades', function () {
    $advisor = new User(['role' => UserRole::ORIENTADOR]);
    $advisor->setAttribute('id', 10);

    $otherAdvisor = new User(['role' => UserRole::ORIENTADOR]);
    $otherAdvisor->setAttribute('id', 11);

    $internship = new Internship(['advisor_id' => 10]);
    $policy = new InternshipPolicy;

    expect($policy->updateAdvisorGrades($advisor, $internship))->toBeTrue()
        ->and($policy->updateAdvisorGrades($otherAdvisor, $internship))->toBeFalse();
});

test('a coordinator assigned as advisor can update report and presentation grades', function () {
    $coordinator = new User(['role' => UserRole::COORDENADOR]);
    $coordinator->setAttribute('id', 20);

    $internship = new Internship(['advisor_id' => 20]);

    expect((new InternshipPolicy)->updateAdvisorGrades($coordinator, $internship))->toBeTrue();
});
