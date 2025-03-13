import os
import json

# Get the absolute path of the JSON file
dir_path = os.path.dirname(os.path.abspath(__file__))
input_file = os.path.join(dir_path, "foodslist_pre.json")
output_file = os.path.join(dir_path, "foodslist_fully_unique.json")

# Load the JSON data
with open(input_file, "r", encoding="utf-8") as file:
    data = json.load(file)

# Remove duplicates by converting lists to sets and back to lists
data["veggy"] = list(set(data.get("veggy", [])))
data["non_veggy"] = list(set(data.get("non_veggy", [])))

# Save the cleaned data
with open(output_file, "w", encoding="utf-8") as file:
    json.dump(data, file, indent=4, ensure_ascii=False)

print(f"Processed file saved as {output_file}")
