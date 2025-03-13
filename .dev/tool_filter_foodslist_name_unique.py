import os
import json

# Get the absolute path of the JSON file
dir_path = os.path.dirname(os.path.abspath(__file__))
input_file = os.path.join(dir_path, "foodslist_pre.json")
output_file = os.path.join(dir_path, "foodslist_name_unique.json")

# Load the JSON data
with open(input_file, "r", encoding="utf-8") as file:
    data = json.load(file)

def filter_unique_foods(food_list):
    unique_foods = {}
    for entry in food_list:
        food_name, _, food_desc = entry.partition("; ")
        if food_name not in unique_foods:
            unique_foods[food_name] = entry
    return list(unique_foods.values())

# Filter for unique food names
data["veggy"] = filter_unique_foods(data.get("veggy", []))
data["non_veggy"] = filter_unique_foods(data.get("non_veggy", []))

# Save the cleaned data
with open(output_file, "w", encoding="utf-8") as file:
    json.dump(data, file, indent=4, ensure_ascii=False)

print(f"Processed file saved as {output_file}")
