<?php
class EnergyData {
    private $db;
    private $defaults = [
        'current_usage' => 2.4,
        'solar_generated' => 18.7,
        'battery_stored' => 7.5,
        'energy_cost' => 127.50,
        'air_conditioning' => 42,
        'water_heater' => 28,
        'lighting' => 15,
        'kitchen' => 15,
        'efficiency_score' => 85
    ];
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    private function getValue($column, $default) {
        try {
            $stmt = $this->db->query("SELECT $column FROM energy_data ORDER BY id DESC LIMIT 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result[$column] ?? $default;
        } catch (PDOException $e) {
            return $default;
        }
    }
    
    public function getCurrentUsage() {
        return $this->getValue('current_usage', $this->defaults['current_usage']);
    }
    
    public function getSolarGenerated() {
        return $this->getValue('solar_generated', $this->defaults['solar_generated']);
    }
    
    public function getBatteryStored() {
        return $this->getValue('battery_stored', $this->defaults['battery_stored']);
    }
    
    public function getEnergyCost() {
        return $this->getValue('energy_cost', $this->defaults['energy_cost']);
    }
    
    public function getBatteryPercentage() {
        return round(($this->getBatteryStored() / 10) * 100);
    }
    
    public function getPercentageChange($field) {
        $changes = ['usage' => -12, 'solar' => 8, 'cost' => 5];
        return $changes[$field] ?? 0;
    }
    
    public function getTopConsumers() {
        try {
            $stmt = $this->db->query("SELECT air_conditioning, water_heater, lighting, kitchen FROM energy_data ORDER BY id DESC LIMIT 1");
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'air_conditioning' => $data['air_conditioning'] ?? $this->defaults['air_conditioning'],
                'water_heater' => $data['water_heater'] ?? $this->defaults['water_heater'],
                'lighting' => $data['lighting'] ?? $this->defaults['lighting'],
                'kitchen' => $data['kitchen'] ?? $this->defaults['kitchen']
            ];
        } catch (PDOException $e) {
            return [
                'air_conditioning' => $this->defaults['air_conditioning'],
                'water_heater' => $this->defaults['water_heater'],
                'lighting' => $this->defaults['lighting'],
                'kitchen' => $this->defaults['kitchen']
            ];
        }
    }
    
    public function getEfficiencyScore() {
        return [
            'efficiency_score' => $this->defaults['efficiency_score'],
            'peak_optimization' => 'Good',
            'solar_utilization' => 'Excellent'
        ];
    }
}
?>