<?php
// src/Service/HealthAdvisor.php

namespace App\Service;

use App\Entity\Fichepatient;

class HealthAdvisor
{
    public function provideHealthAdvice(Fichepatient $fichepatient): string
    {
        // Analyze health data and provide personalized advice
        $advice = '';



        $bmi = $this->calculateBMI($fichepatient->getWeight(), $fichepatient->getHeight());
        
        // Example: Check weight and suggest exercises or diet modifications
        if ($bmi > 24.9) {
            $advice .= "<b>Health Advice:</b><ul><li>Your BMI is {$bmi}, which indicates that you are overweight. Consider reducing calorie intake and doing more exercises.</li>";
        } else if ($bmi < 18.5) {
            $advice .= "<b>Health Advice:</b><ul><li>Your BMI is {$bmi}, which indicates that you are underweight. Consider increasing calorie intake and doing strength training exercises.</li>";
        } else {
            $advice .= "<b>Health Advice:</b><ul><li>Your BMI is is {$bmi}, which is within a normal range. Keep up the good work!</li>";
        }



        $waterIntake = $this->calculateWaterIntake($fichepatient->getWeight());
        $advice .= "<li>Drink at least {$waterIntake} milliliters of water per day for proper hydration.</li>";

        // Example: Check weight and suggest exercises or diet modifications
        if ($fichepatient->getWeight() > 80) {
            $advice .= "<b>Health Advice:</b><ul><li>Your weight is above normal. Consider reducing calorie intake and doing more exercises.</li>";
        } else {
            $advice .= "<b>Health Advice:</b><ul><li>Your weight is within normal range. Keep up the good work!</li>";
        }

        // Analyze muscle mass and suggest strength training
        if ($fichepatient->getMuscleMass() < 50) {
            $advice .= "<li>Your muscle mass is low. Consider incorporating strength training exercises into your routine.</li>";
        } else {
            $advice .= "<li>Your muscle mass is adequate. Keep doing resistance exercises to maintain it.</li>";
        }

        // Analyze height and suggest posture improvement exercises
        if ($fichepatient->getHeight() > 160) {
            $advice .= "<li>Your height indicates good posture. However, you can still benefit from posture improvement exercises.</li>";
        } else {
            $advice .= "<li>Your height might indicate poor posture. Consider exercises to improve your posture.</li>";
        }

        // Analyze allergies and suggest avoiding allergens
        if ($fichepatient->getAllergies()) {
            $advice .= "<li>Be mindful of your allergies and avoid allergens to prevent adverse reactions.</li>";
        } 

        if ($fichepatient->getAllergies() && strpos($fichepatient->getAllergies(), 'fraise') !== false) {
            $advice .= "<b>Health Advice:</b><ul><li>Avoid strawberries and any foods containing strawberries to prevent allergic reactions.</li></ul>";
        }
        
        // Analyze illnesses and suggest appropriate actions
        if ($fichepatient->getIllnesses()) {
            $advice .= "<li>Manage your illnesses effectively. Follow your doctor's advice and take necessary precautions.</li>";
        }

        // Analyze daily diet and suggest improvements
        $dailyDiet = $fichepatient->getBreakfast() . ', ' . $fichepatient->getMidday() . ', ' . $fichepatient->getDinner() . ', ' . $fichepatient->getSnacks();
        if (strpos($dailyDiet, 'fast food') !== false || strpos($dailyDiet, 'sugary snacks') !== false) {
            $advice .= "<li>Your daily diet contains unhealthy items like fast food and sugary snacks. Consider adding more fruits, vegetables, and whole grains.</li>";
        }

        // Analyze calorie intake and suggest adjustments
        if ($fichepatient->getCalories() > 2000) {
            $advice .= "<li>Your daily calorie intake seems high. Consider reducing it for better health.</li>";
        }

        // Add more analysis and advice based on other health data
        
        $advice .= "</ul>";
        return $advice;
    }
    private function calculateBMI($weight, $height): float
    {
        $heightMeters = $height / 100; // Convert height from cm to meters
        return round($weight / ($heightMeters * $heightMeters), 1);
    }

    private function calculateWaterIntake($weight): int
    {
        // Daily water intake recommendation (30-35 ml per kg of body weight)
        return round(30 * $weight);
    }
}
